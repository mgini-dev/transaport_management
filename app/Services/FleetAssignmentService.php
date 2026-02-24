<?php

namespace App\Services;

use App\Models\Fleet;
use App\Models\Order;
use App\Models\OrderLeg;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FleetAssignmentService
{
    public function __construct(private readonly NotificationRoutingService $notificationRoutingService)
    {
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function assign(Order $order, array $payload, User $actor): OrderLeg
    {
        if ($order->trip->status !== 'open') {
            throw new RuntimeException('Cannot assign fleet to orders in a closed trip.');
        }

        if (! in_array($order->status, ['processing', 'assigned'], true)) {
            throw new RuntimeException('Order must be processing or assigned before fleet assignment.');
        }

        /** @var Fleet $fleet */
        $fleet = Fleet::query()->findOrFail((int) $payload['fleet_id']);

        $fleetHasActiveLeg = OrderLeg::query()
            ->where('fleet_id', $fleet->id)
            ->where('status', 'active')
            ->exists();

        if ($fleetHasActiveLeg) {
            throw new RuntimeException('Selected fleet is already assigned to an active leg.');
        }

        return DB::transaction(function () use ($order, $payload, $fleet, $actor) {
            $sequence = (int) OrderLeg::query()
                ->where('order_id', $order->id)
                ->max('leg_sequence') + 1;

            $leg = OrderLeg::query()->create([
                'order_id' => $order->id,
                'fleet_id' => $fleet->id,
                'driver_id' => $payload['driver_id'] ?? null,
                'leg_sequence' => $sequence,
                'origin_address' => $payload['origin_address'],
                'destination_address' => $payload['destination_address'],
                'distance_km' => $payload['distance_km'] ?? null,
                'status' => 'active',
            ]);

            $fleet->update(['status' => 'unavailable']);
            $order->update(['status' => 'assigned']);

            $this->notificationRoutingService->notifyPermission(
                permission: 'fuel.create',
                title: 'Fleet assignment created',
                message: "Order {$order->order_number} has a new assigned leg and may require fuel requisition.",
                type: 'order.leg.assigned',
                meta: ['order_id' => $order->id, 'leg_id' => $leg->id, 'fleet_id' => $fleet->id]
            );

            return $leg;
        });
    }

    public function complete(OrderLeg $leg): OrderLeg
    {
        return DB::transaction(function () use ($leg) {
            $leg->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $hasActiveLeg = OrderLeg::query()
                ->where('fleet_id', $leg->fleet_id)
                ->where('status', 'active')
                ->exists();

            if (! $hasActiveLeg) {
                $leg->fleet()->update(['status' => 'available']);
            }

            return $leg->refresh();
        });
    }
}
