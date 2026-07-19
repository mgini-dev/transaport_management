<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetDriverHistory extends Model
{
    use HasEncryptedId, HasFactory;

    protected $table = 'fleet_driver_history';

    protected $fillable = [
        'fleet_id',
        'driver_id',
        'assigned_at',
        'unassigned_at',
        'start_odometer',
        'end_odometer',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
            'start_odometer' => 'decimal:2',
            'end_odometer' => 'decimal:2',
        ];
    }

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
