<?php

namespace App\Services;

use App\Models\FuelBalance;
use App\Models\FuelRequisition;
use App\Models\Order;
use App\Models\OrderLeg;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FuelRequisitionService
{
    public function __construct(
        private readonly NotificationRoutingService $notificationRoutingService,
        private readonly DistanceService $distanceService
    )
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): FuelRequisition
    {
        return DB::transaction(function () use ($payload, $actor) {
            $type = (string) ($payload['requisition_type'] ?? 'order_based');
            $fleetId = (int) $payload['fleet_id'];
            $order = null;

            if ($type === 'order_based') {
                $order = Order::query()->with('trip')->findOrFail((int) $payload['order_id']);

                if ($order->trip->status !== 'open') {
                    throw new \RuntimeException('Cannot create fuel requisition for an order in a closed trip.');
                }

                if ($order->status !== 'assigned') {
                    throw new \RuntimeException('Fuel requisition can only be created for assigned orders.');
                }

                $fleetAssigned = OrderLeg::query()
                    ->where('order_id', $order->id)
                    ->where('fleet_id', $fleetId)
                    ->exists();

                if (! $fleetAssigned) {
                    throw new \RuntimeException('Selected fleet is not assigned to this order.');
                }

                $baseDistance = $order->distance_km !== null
                    ? (float) $order->distance_km
                    : $this->distanceService->calculateKm((string) $order->origin_address, (string) $order->destination_address);

                if ($baseDistance === null) {
                    throw new \RuntimeException('Unable to determine order distance for this requisition.');
                }

                if ($order->distance_km === null) {
                    $order->update(['distance_km' => $baseDistance]);
                }
            } else {
                $originAddress = trim((string) ($payload['origin_address'] ?? ''));
                $destinationAddress = trim((string) ($payload['destination_address'] ?? ''));

                if ($originAddress === '' || $destinationAddress === '') {
                    throw new \RuntimeException('Origin and destination are required for fleet-only requisition.');
                }

                $fleetEligible = OrderLeg::query()
                    ->where('fleet_id', $fleetId)
                    ->where('status', 'completed')
                    ->exists();

                if (! $fleetEligible) {
                    throw new \RuntimeException('Fleet-only requisition requires a fleet with at least one completed order leg.');
                }

                $baseDistance = $this->distanceService->calculateKm($originAddress, $destinationAddress);
                if ($baseDistance === null) {
                    throw new \RuntimeException('Unable to determine route distance for fleet-only requisition.');
                }
            }

            $additionalDistance = (float) ($payload['additional_distance_km'] ?? 0);
            $totalDistance = $baseDistance + $additionalDistance;
            $estimatedFuelLitres = round($totalDistance * 0.5, 2);

            $availableBalanceLitres = (float) (FuelBalance::query()
                ->where('fleet_id', $fleetId)
                ->latest('id')
                ->value('remaining_litres') ?? 0);

            $requestedLitres = max(round($estimatedFuelLitres - $availableBalanceLitres, 2), 0);
            $price = (float) $payload['fuel_price'];
            $discount = (float) ($payload['discount'] ?? 0);
            $grossAmount = $requestedLitres * $price;
            $netAmount = max(round($grossAmount - $discount, 2), 0);

            $requisitionPayload = [
                ...Arr::only($payload, [
                    'order_id',
                    'requisition_type',
                    'fleet_id',
                    'fuel_station',
                    'fuel_price',
                    'discount',
                    'payment_channel',
                    'payment_account',
                    'origin_address',
                    'destination_address',
                ]),
                'order_id' => $order?->id,
                'requisition_type' => $type,
                'additional_litres' => $requestedLitres,
                'requested_by' => $actor->id,
                'total_amount' => $netAmount,
                'status' => 'submitted',
            ];

            if (Schema::hasColumn('fuel_requisitions', 'base_distance_km')) {
                $requisitionPayload['base_distance_km'] = round($baseDistance, 2);
                $requisitionPayload['additional_distance_km'] = round($additionalDistance, 2);
                $requisitionPayload['total_distance_km'] = round($totalDistance, 2);
                $requisitionPayload['estimated_fuel_litres'] = $estimatedFuelLitres;
                $requisitionPayload['available_balance_litres'] = round($availableBalanceLitres, 2);
            }

            $requisition = FuelRequisition::query()->create($requisitionPayload);

            $this->notificationRoutingService->notifyPermission(
                permission: 'fuel.approve.supervisor',
                title: 'Fuel requisition needs supervisor review',
                message: "Fuel requisition #{$requisition->id} has been submitted.",
                type: 'fuel.submitted',
                meta: ['requisition_id' => $requisition->id, 'order_id' => $requisition->order_id, 'requisition_type' => $requisition->requisition_type],
                filter: function (User $user) use ($requisition): bool {
                    if ($requisition->order_id === null) {
                        return $user->can('fuel.view_all');
                    }
                    return $user->can('fuel.view_all') || $requisition->requested_by === $user->id || $user->can('orders.view_all');
                },
                excludeUserId: $actor->id
            );

            return $requisition;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordBalance(array $payload, User $actor): FuelBalance
    {
        return DB::transaction(function () use ($payload, $actor) {
            $order = \App\Models\Order::query()->findOrFail((int) $payload['order_id']);
            $fleetId = (int) $payload['fleet_id'];

            if ($order->status !== 'completed') {
                throw new \RuntimeException('Fuel balance can only be recorded for completed orders.');
            }

            $fleetAssigned = OrderLeg::query()
                ->where('order_id', $order->id)
                ->where('fleet_id', $fleetId)
                ->exists();

            if (! $fleetAssigned) {
                throw new \RuntimeException('Selected fleet is not assigned to the selected order.');
            }

            return FuelBalance::query()->create([
                'order_id' => $order->id,
                'fleet_id' => $fleetId,
                'remaining_litres' => (float) $payload['remaining_litres'],
                'remarks' => $payload['remarks'] ?? null,
                'updated_by' => $actor->id,
            ]);
        });
    }

    public function supervisorDecision(FuelRequisition $requisition, bool $approved, User $actor, ?string $remarks = null): FuelRequisition
    {
        if ($requisition->status !== 'submitted') {
            return $requisition;
        }

        $requisition->update([
            'status' => $approved ? 'supervisor_approved' : 'supervisor_rejected',
            'supervisor_id' => $actor->id,
            'supervisor_remarks' => $remarks,
            'supervisor_reviewed_at' => now(),
        ]);

        if ($approved) {
            $this->notificationRoutingService->notifyPermission(
                permission: 'fuel.approve.accounting',
                title: 'Fuel requisition needs accounting approval',
                message: "Fuel requisition #{$requisition->id} was approved by supervisor.",
                type: 'fuel.supervisor_approved',
                meta: ['requisition_id' => $requisition->id, 'order_id' => $requisition->order_id],
                filter: function (User $user) use ($requisition): bool {
                    if ($requisition->order_id === null) {
                        return $user->can('fuel.view_all');
                    }
                    return $user->can('fuel.view_all') || $requisition->requested_by === $user->id || $user->can('orders.view_all');
                },
                excludeUserId: $actor->id
            );
        } else {
            $requisition->requester?->notify(new \App\Notifications\WorkflowStageNotification(
                title: 'Fuel requisition rejected by supervisor',
                message: "Fuel requisition #{$requisition->id} was rejected by supervisor.",
                type: 'fuel.supervisor_rejected',
                meta: ['requisition_id' => $requisition->id, 'order_id' => $requisition->order_id]
            ));
        }

        return $requisition->refresh();
    }

    public function accountantDecision(FuelRequisition $requisition, bool $approved, User $actor, ?string $remarks = null): FuelRequisition
    {
        if ($requisition->status !== 'supervisor_approved') {
            return $requisition;
        }

        $requisition->update([
            'status' => $approved ? 'accountant_approved' : 'accountant_rejected',
            'accountant_id' => $actor->id,
            'accountant_remarks' => $remarks,
            'accountant_reviewed_at' => now(),
        ]);

        $requisition->requester?->notify(new \App\Notifications\WorkflowStageNotification(
            title: 'Fuel requisition accounting decision',
            message: "Fuel requisition #{$requisition->id} is now {$requisition->status}.",
            type: 'fuel.accountant_decision',
            meta: ['requisition_id' => $requisition->id, 'order_id' => $requisition->order_id]
        ));

        if ($approved && $requisition->order_id) {
            $order = Order::query()->with('legs')->find($requisition->order_id);
            if ($order && $order->status === 'assigned' && $order->legs()->exists()) {
                $previousStatus = $order->status;
                $order->update(['status' => 'transportation']);
                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'from_status' => $previousStatus,
                    'to_status' => 'transportation',
                    'changed_by' => $actor->id,
                    'remarks' => 'Moved to transportation after accountant approved fuel requisition.',
                ]);
            }
        }

        return $requisition->refresh();
    }
}
