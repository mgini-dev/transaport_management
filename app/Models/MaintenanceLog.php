<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceLog extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'fleet_id',
        'service_type',
        'odometer_reading',
        'cost',
        'performed_at',
        'remarks',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'odometer_reading' => 'decimal:2',
            'cost' => 'decimal:2',
        ];
    }

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaintenanceItem::class);
    }
}
