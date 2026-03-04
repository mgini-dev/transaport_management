<?php

namespace App\Services;

use App\Models\FuelBalance;
use App\Models\Order;
use App\Models\OrderLeg;
use App\Models\OrderStatusHistory;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(
        private readonly DistanceService $distanceService,
        private readonly NotificationRoutingService $notificationRoutingService
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): Order
    {
        $trip = Trip::query()->findOrFail((int) $payload['trip_id']);

        if ($trip->status !== 'open') {
            throw new RuntimeException('Cannot create an order in a closed trip.');
        }

        return DB::transaction(function () use ($payload, $actor) {
            $distanceKm = $this->distanceService->calculateKm(
                (string) $payload['origin_address'],
                (string) $payload['destination_address']
            );

            $order = Order::query()->create([
                ...Arr::only($payload, [
                    'trip_id',
                    'customer_id',
                    'cargo_type',
                    'cargo_description',
                    'weight_tons',
                    'agreed_price',
                    'origin_address',
                    'destination_address',
                    'expected_loading_date',
                    'expected_leaving_date',
                    'remarks',
                ]),
                'order_number' => $this->generateOrderNumber(),
                'distance_km' => $distanceKm,
                'estimated_fuel_litres' => $distanceKm !== null ? round($distanceKm * 0.5, 2) : null,
                'status' => 'created',
                'created_by' => $actor->id,
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'created',
                'changed_by' => $actor->id,
                'remarks' => 'Order created.',
            ]);

            $this->notificationRoutingService->notifyPermission(
                permission: 'orders.process',
                title: 'New order requires processing',
                message: "Order {$order->order_number} was created and is ready for processing.",
                type: 'order.created',
                meta: ['order_id' => $order->id, 'trip_id' => $order->trip_id],
                filter: fn (User $user) => $user->can('orders.view_all') || $order->created_by === $user->id,
                excludeUserId: $actor->id
            );

            return $order;
        });
    }

    public function updateStatus(Order $order, string $toStatus, User $actor, ?string $remarks = null): Order
    {
        $fromStatus = $order->status;

        DB::transaction(function () use ($order, $toStatus, $actor, $remarks, $fromStatus) {
            $order->update([
                'status' => $toStatus,
                'completed_at' => $toStatus === 'completed' ? now() : null,
            ]);

            if ($toStatus === 'completed') {
                $activeLegs = OrderLeg::query()
                    ->where('order_id', $order->id)
                    ->where('status', 'active')
                    ->get();

                foreach ($activeLegs as $leg) {
                    $leg->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                    $hasOtherActiveForFleet = OrderLeg::query()
                        ->where('fleet_id', $leg->fleet_id)
                        ->where('status', 'active')
                        ->exists();

                    if (! $hasOtherActiveForFleet) {
                        $leg->fleet()->update(['status' => 'available']);
                    }
                }
            }

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => $actor->id,
                'remarks' => $remarks,
            ]);
        });

        return $order->refresh();
    }

    public function calculateAndStoreDistance(Order $order): Order
    {
        $distanceKm = $this->distanceService->calculateKm(
            (string) $order->origin_address,
            (string) $order->destination_address
        );

        if ($distanceKm === null) {
            throw new RuntimeException('Distance could not be calculated right now. Please try again shortly.');
        }

        $order->update([
            'distance_km' => $distanceKm,
            'estimated_fuel_litres' => round($distanceKm * 0.5, 2),
        ]);

        return $order->refresh();
    }

    /**
     * @param  array{
     *   fleet_balances: array<int, array{fleet_id:int,remaining_litres:float}>,
     *   completion_comment:?string,
     *   completion_document_path:string
     * }  $payload
     */
    public function completeTransportation(Order $order, array $payload, User $actor): Order
    {
        if ($order->status !== 'transportation') {
            throw new RuntimeException('Order must be in transportation before completion.');
        }

        $assignedFleetIds = OrderLeg::query()
            ->where('order_id', $order->id)
            ->pluck('fleet_id')
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
        $submittedFleetIds = collect($payload['fleet_balances'] ?? [])
            ->pluck('fleet_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        sort($assignedFleetIds);
        sort($submittedFleetIds);

        if ($assignedFleetIds !== $submittedFleetIds) {
            throw new RuntimeException('Please record fuel balance for each assigned fleet before completing the order.');
        }

        return DB::transaction(function () use ($order, $payload, $actor) {
            foreach ($payload['fleet_balances'] as $balanceRow) {
                FuelBalance::query()->create([
                    'order_id' => $order->id,
                    'fleet_id' => (int) $balanceRow['fleet_id'],
                    'remaining_litres' => (float) $balanceRow['remaining_litres'],
                    'remarks' => $payload['completion_comment'] ?? null,
                    'updated_by' => $actor->id,
                ]);
            }

            $order->update([
                'completion_document_path' => $payload['completion_document_path'],
                'completion_comment' => $payload['completion_comment'] ?? null,
                'completed_by' => $actor->id,
            ]);

            return $this->updateStatus($order, 'completed', $actor, $payload['completion_comment'] ?? 'Order completed with signed delivery note and fuel balance.');
        });
    }

    private function generateOrderNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $sequence = Order::query()->whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('ORD-%s-%04d', $datePrefix, $sequence);
    }
}
