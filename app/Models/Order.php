<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'trip_id',
        'customer_id',
        'order_number',
        'cargo_type',
        'cargo_description',
        'weight_tons',
        'agreed_price',
        'origin_address',
        'destination_address',
        'expected_loading_date',
        'expected_leaving_date',
        'distance_km',
        'estimated_fuel_litres',
        'status',
        'remarks',
        'created_by',
        'completed_at',
        'completion_document_path',
        'delivery_note_issued_at',
        'delivery_note_issued_by',
        'completion_comment',
        'completed_by',
    ];

    protected function casts(): array
    {
        return [
            'expected_loading_date' => 'date',
            'expected_leaving_date' => 'date',
            'completed_at' => 'datetime',
            'delivery_note_issued_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function deliveryNoteIssuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_note_issued_by');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function legs(): HasMany
    {
        return $this->hasMany(OrderLeg::class);
    }

    public function fuelRequisitions(): HasMany
    {
        return $this->hasMany(FuelRequisition::class);
    }

    public function fuelBalances(): HasMany
    {
        return $this->hasMany(FuelBalance::class);
    }
}
