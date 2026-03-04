<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Fleet;
use App\Models\Order;
use App\Models\OrderLeg;
use App\Models\OrderStatusHistory;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TripService
{
    public function create(User $actor): Trip
    {
        return Trip::query()->create([
            'trip_number' => $this->generateTripNumber(),
            'status' => 'open',
            'created_by' => $actor->id,
        ]);
    }

    /**
     * @param  array<int, string>  $orderExplanations
     */
    public function close(Trip $trip, User $actor, bool $forceClose = false, array $orderExplanations = []): Trip
    {
        if ($trip->status === 'closed') {
            return $trip;
        }

        DB::transaction(function () use ($trip, $actor, $forceClose, $orderExplanations): void {
            $trip->update([
                'status' => 'closed',
                'closed_by' => $actor->id,
                'closed_at' => Carbon::now(),
            ]);

            if (! $forceClose) {
                return;
            }

            $incompleteOrders = Order::query()
                ->where('trip_id', $trip->id)
                ->where('status', '!=', 'completed')
                ->get();

            foreach ($incompleteOrders as $order) {
                $explanation = trim((string) ($orderExplanations[$order->id] ?? ''));
                $previousStatus = (string) $order->status;
                $order->update([
                    'status' => 'incomplete',
                    'remarks' => trim((string) $order->remarks."\n[Trip Force Close] ".$explanation),
                ]);

                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'from_status' => $previousStatus,
                    'to_status' => 'incomplete',
                    'changed_by' => $actor->id,
                    'remarks' => 'Trip was force-closed before order completion. Reason: '.$explanation,
                ]);
            }

            $activeLegs = OrderLeg::query()
                ->whereIn('order_id', $incompleteOrders->pluck('id'))
                ->where('status', 'active')
                ->get();

            if ($activeLegs->isEmpty()) {
                return;
            }

            $activeLegs->each(function (OrderLeg $leg): void {
                $leg->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            });

            $fleetIds = $activeLegs->pluck('fleet_id')->filter()->unique()->values()->all();
            if (! empty($fleetIds)) {
                Fleet::query()->whereIn('id', $fleetIds)->update(['status' => 'available']);
            }

            $driverIds = $activeLegs->pluck('driver_id')->filter()->unique()->values()->all();
            if (! empty($driverIds)) {
                Driver::query()->whereIn('id', $driverIds)->update(['fleet_id' => null]);
            }
        });

        return $trip->refresh();
    }

    private function generateTripNumber(): string
    {
        $datePrefix = now()->format('Ymd');
        $sequence = Trip::query()->whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('TRP-%s-%04d', $datePrefix, $sequence);
    }
}
