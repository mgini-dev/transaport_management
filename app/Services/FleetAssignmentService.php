<?php

namespace App\Services;

use App\Models\Fleet;
use App\Models\Order;
use App\Models\OrderLeg;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        if (! in_array($order->status, ['created', 'processing', 'assigned'], true)) {
            throw new RuntimeException('Order must be created, processing, or assigned before fleet assignment.');
        }

        /** @var Fleet $fleet */
        $fleet = Fleet::query()->findOrFail((int) $payload['fleet_id']);

        if (! Schema::hasColumn('fleets', 'trailer_number')) {
            throw new RuntimeException('Fleet trailer support is not ready. Please run database migrations.');
        }

        if (blank($fleet->trailer_number)) {
            throw new RuntimeException('Selected fleet must have a trailer number before assignment. Update fleet details first.');
        }

        $driverId = (int) ($payload['driver_id'] ?? 0);
        if ($driverId <= 0) {
            throw new RuntimeException('A driver is required when assigning a fleet.');
        }

        /** @var Driver $driver */
        $driver = Driver::query()->findOrFail($driverId);

        if (! $driver->is_active) {
            throw new RuntimeException('Selected driver is inactive and cannot be assigned.');
        }

        $driverHasActiveLeg = OrderLeg::query()
            ->where('driver_id', $driver->id)
            ->where('status', 'active')
            ->exists();

        if ($driverHasActiveLeg) {
            throw new RuntimeException('Selected driver is already assigned to another active leg.');
        }

        $fleetHasActiveLeg = OrderLeg::query()
            ->where('fleet_id', $fleet->id)
            ->where('status', 'active')
            ->exists();

        if ($fleetHasActiveLeg) {
            throw new RuntimeException('Selected fleet is already assigned to an active leg.');
        }

        return DB::transaction(function () use ($order, $payload, $fleet, $driver, $actor) {
            $sequence = (int) OrderLeg::query()
                ->where('order_id', $order->id)
                ->max('leg_sequence') + 1;

            $legPayload = [
                'order_id' => $order->id,
                'fleet_id' => $fleet->id,
                'driver_id' => $driver->id,
                'leg_sequence' => $sequence,
                'origin_address' => (string) $order->origin_address,
                'destination_address' => (string) $order->destination_address,
                'distance_km' => $payload['distance_km'] ?? $order->distance_km,
                'status' => 'active',
            ];

            if (Schema::hasColumn('order_legs', 'trailer_number')) {
                $legPayload['trailer_number'] = $fleet->trailer_number;
            }

            $leg = OrderLeg::query()->create($legPayload);

            // Ensure one fleet maps to only one current driver at a time.
            Driver::query()
                ->where('fleet_id', $fleet->id)
                ->where('id', '!=', $driver->id)
                ->update(['fleet_id' => null]);

            // Driver-to-fleet mapping is controlled at leg assignment time.
            $driver->update(['fleet_id' => $fleet->id]);
            $fleet->update(['status' => 'unavailable']);
            $order->update(['status' => 'assigned']);

            $this->notificationRoutingService->notifyPermission(
                permission: 'fuel.create',
                title: 'Fleet assignment created',
                message: "Order {$order->order_number} has a new assigned leg and may require fuel requisition.",
                type: 'order.leg.assigned',
                meta: ['order_id' => $order->id, 'leg_id' => $leg->id, 'fleet_id' => $fleet->id],
                filter: fn (User $user) => $user->can('orders.view_all') || $order->created_by === $user->id || $user->can('fleet.view_all'),
                excludeUserId: $actor->id
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

            if ($leg->driver_id) {
                $driverHasActiveLeg = OrderLeg::query()
                    ->where('driver_id', $leg->driver_id)
                    ->where('status', 'active')
                    ->exists();

                if (! $driverHasActiveLeg) {
                    Driver::query()
                        ->whereKey($leg->driver_id)
                        ->update(['fleet_id' => null]);
                }
            }

            return $leg->refresh();
        });
    }
}
