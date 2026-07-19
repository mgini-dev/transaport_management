<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Fleet;
use App\Models\FleetDriverHistory;
use App\Models\MaintenanceLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FleetService
{
    /**
     * Update the current odometer reading for a fleet.
     * This will also trigger service predictions.
     */
    public function updateOdometer(Fleet $fleet, float $reading): Fleet
    {
        if ($reading < $fleet->current_odometer) {
            // Log warning or throw exception if odometer is reversed?
            // For now, let's just ensure it doesn't go backwards.
            return $fleet;
        }

        $fleet->current_odometer = $reading;
        
        // Recalculate next service if not set
        if (!$fleet->next_service_due_km || $fleet->next_service_due_km <= $fleet->current_odometer) {
            $this->predictNextService($fleet);
        }

        $fleet->save();
        return $fleet;
    }

    /**
     * Record a driver assignment to a fleet.
     */
    public function assignDriver(Fleet $fleet, Driver $driver): FleetDriverHistory
    {
        return DB::transaction(function () use ($fleet, $driver) {
            // Close any existing active assignments for this fleet
            FleetDriverHistory::query()
                ->where('fleet_id', $fleet->id)
                ->whereNull('unassigned_at')
                ->update([
                    'unassigned_at' => now(),
                    'end_odometer' => $fleet->current_odometer,
                ]);

            // Create new assignment
            return FleetDriverHistory::query()->create([
                'fleet_id' => $fleet->id,
                'driver_id' => $driver->id,
                'assigned_at' => now(),
                'start_odometer' => $fleet->current_odometer,
            ]);
        });
    }

    /**
     * Record a maintenance log and update service tracking.
     */
    public function recordMaintenance(Fleet $fleet, array $data, ?User $actor = null): MaintenanceLog
    {
        return DB::transaction(function () use ($fleet, $data, $actor) {
            $log = MaintenanceLog::query()->create([
                'fleet_id' => $fleet->id,
                'service_type' => $data['service_type'],
                'odometer_reading' => $data['odometer_reading'],
                'cost' => $data['cost'] ?? 0,
                'performed_at' => $data['performed_at'] ?? now(),
                'remarks' => $data['remarks'] ?? null,
                'performed_by' => $actor?->id ?? auth()->id(),
            ]);

            // Save individual items if provided
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $log->items()->create([
                        'category' => $item['category'],
                        'description' => $item['description'],
                        'cost' => $item['cost'],
                        'installed_at_km' => $data['odometer_reading'],
                        'lifespan_km' => $item['lifespan_km'] ?? null,
                        'next_due_km' => isset($item['lifespan_km']) ? ($data['odometer_reading'] + $item['lifespan_km']) : null,
                    ]);
                }
            }

            // Smart Update: Reset counters and bring back to available status
            $fleet->update([
                'last_service_odometer' => $data['odometer_reading'],
                'current_odometer' => max($fleet->current_odometer, $data['odometer_reading']),
                'next_service_due_km' => $data['odometer_reading'] + $fleet->oil_change_interval_km,
                'status' => 'available',
            ]);

            return $log;
        });
    }

    /**
     * Defer maintenance for a fleet.
     */
    public function deferMaintenance(Fleet $fleet, float $extraKm, string $reason): void
    {
        DB::transaction(function () use ($fleet, $extraKm, $reason) {
            $fleet->increment('next_service_due_km', $extraKm);
            
            // Log the deferral as a special type of "maintenance" record
            $fleet->maintenanceLogs()->create([
                'service_type' => 'deferral',
                'odometer_reading' => $fleet->current_odometer,
                'cost' => 0,
                'performed_at' => now(),
                'remarks' => "Service deferred by {$extraKm}km. Reason: {$reason}",
                'performed_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Predict the next service due based on the last service and interval.
     */
    public function predictNextService(Fleet $fleet): void
    {
        $interval = $fleet->oil_change_interval_km ?? 5000;
        $lastService = $fleet->last_service_odometer ?? 0;
        
        $fleet->next_service_due_km = $lastService + $interval;
    }

    /**
     * Calculate fuel efficiency for a fleet over a period.
     * Returns km per liter.
     */
    public function calculateEfficiency(Fleet $fleet): float
    {
        $requisitions = $fleet->fuelRequisitions()
            ->where('status', 'accountant_approved')
            ->where('odometer_reading', '>', 0)
            ->orderBy('odometer_reading', 'desc')
            ->take(2)
            ->get();

        if ($requisitions->count() < 2) {
            return 0;
        }

        $latest = $requisitions[0];
        $previous = $requisitions[1];

        $distance = $latest->odometer_reading - $previous->odometer_reading;
        if ($distance <= 0) {
            return 0;
        }

        return round($distance / $latest->litres, 2);
    }
}
