<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Driver extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'fleet_id',
        'name',
        'license_number',
        'mobile_number',
        'driver_address',
        'contact1_name',
        'contact1_phone',
        'contact1_address',
        'contact2_name',
        'contact2_phone',
        'contact2_address',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
