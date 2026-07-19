<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\MaintenanceItem;

class Fleet extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'fleet_code',
        'vehicle_type',
        'plate_number',
        'trailer_number',
        'capacity_tons',
        'current_odometer',
        'last_service_odometer',
        'oil_change_interval_km',
        'next_service_due_km',
        'fuel_consumption_benchmark',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_odometer' => 'decimal:2',
            'last_service_odometer' => 'decimal:2',
            'next_service_due_km' => 'decimal:2',
            'fuel_consumption_benchmark' => 'decimal:2',
        ];
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function legs(): HasMany
    {
        return $this->hasMany(OrderLeg::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class)->latest('performed_at');
    }

    public function driverHistory(): HasMany
    {
        return $this->hasMany(FleetDriverHistory::class)->latest('assigned_at');
    }

    public function fuelRequisitions(): HasMany
    {
        return $this->hasMany(FuelRequisition::class);
    }

    public function kmsUntilService(): float
    {
        return max(0, $this->next_service_due_km - $this->current_odometer);
    }

    public function serviceStatus(): string
    {
        $remaining = $this->kmsUntilService();
        
        if ($remaining <= 0) {
            return 'overdue';
        }
        
        if ($remaining <= 500) {
            return 'approaching';
        }
        
        return 'good';
    }

    public function scopeNeedsService($query)
    {
        return $query->whereRaw('next_service_due_km - current_odometer <= 500');
    }

    public function scopeOverdue($query)
    {
        return $query->whereRaw('next_service_due_km - current_odometer <= 0');
    }

    /**
     * Get the latest maintenance item for each category for health tracking.
     */
    public function latestComponentItems()
    {
        return MaintenanceItem::query()
            ->whereIn('maintenance_log_id', $this->maintenanceLogs()->pluck('id'))
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('maintenance_items')
                    ->groupBy('category');
            })
            ->get();
    }
}
