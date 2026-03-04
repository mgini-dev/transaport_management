<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRequisition extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'order_id',
        'requisition_type',
        'fleet_id',
        'requested_by',
        'fuel_station',
        'base_distance_km',
        'additional_distance_km',
        'total_distance_km',
        'estimated_fuel_litres',
        'available_balance_litres',
        'additional_litres',
        'fuel_price',
        'discount',
        'total_amount',
        'payment_channel',
        'payment_account',
        'origin_address',
        'destination_address',
        'status',
        'supervisor_id',
        'supervisor_remarks',
        'supervisor_reviewed_at',
        'accountant_id',
        'accountant_remarks',
        'accountant_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'base_distance_km' => 'decimal:2',
            'additional_distance_km' => 'decimal:2',
            'total_distance_km' => 'decimal:2',
            'estimated_fuel_litres' => 'decimal:2',
            'available_balance_litres' => 'decimal:2',
            'additional_litres' => 'decimal:2',
            'fuel_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'supervisor_reviewed_at' => 'datetime',
            'accountant_reviewed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function accountant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accountant_id');
    }
}
