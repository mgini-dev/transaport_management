<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fleet extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'fleet_code',
        'plate_number',
        'trailer_number',
        'capacity_tons',
        'status',
    ];

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function legs(): HasMany
    {
        return $this->hasMany(OrderLeg::class);
    }
}
