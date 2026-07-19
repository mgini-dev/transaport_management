<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'fleet_id',
        'name',
        'license_number',
        'certificate_number',
        'certificate_file_path',
        'certificate_expiry_date',
        'license_file_path',
        'license_expiry_date',
        'license_renewed_place',
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
            'certificate_expiry_date' => 'date',
            'license_expiry_date' => 'date',
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

    public function history(): HasMany
    {
        return $this->hasMany(FleetDriverHistory::class)->latest('assigned_at');
    }

    public function isLicenseExpired(): bool
    {
        if (! $this->license_expiry_date) {
            return false;
        }
        return $this->license_expiry_date->isPast();
    }

    public function isLicenseExpiringSoon(int $days = 10): bool
    {
        if (! $this->license_expiry_date || $this->isLicenseExpired()) {
            return false;
        }
        $daysUntilExpiry = now()->startOfDay()->diffInDays($this->license_expiry_date->startOfDay(), true);
        return $daysUntilExpiry <= $days;
    }
}
