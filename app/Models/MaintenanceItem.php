<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceItem extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'maintenance_log_id',
        'category',
        'description',
        'cost',
        'installed_at_km',
        'lifespan_km',
        'next_due_km',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'installed_at_km' => 'decimal:2',
            'lifespan_km' => 'decimal:2',
            'next_due_km' => 'decimal:2',
        ];
    }

    public function maintenanceLog(): BelongsTo
    {
        return $this->belongsTo(MaintenanceLog::class);
    }
}
