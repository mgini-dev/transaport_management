<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasEncryptedId, HasFactory;

    public const GENDERS = ['male', 'female'];
    public const MARITAL_STATUSES = ['single', 'married', 'divorced', 'widowed', 'separated', 'other'];
    public const EMPLOYMENT_STATUSES = ['active', 'probation', 'on_leave', 'suspended', 'terminated', 'resigned', 'contract_expired'];

    protected $fillable = [
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'phone_number',
        'email',
        'address',
        'gender',
        'marital_status',
        'date_of_birth',
        'position_title',
        'date_employed',
        'contract_duration_months',
        'contract_end_date',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'salary_net',
        'tin_number',
        'nssf_number',
        'photo_path',
        'cv_path',
        'employment_status',
        'status_effective_date',
        'status_note',
        'terminated_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_employed' => 'date',
            'contract_end_date' => 'date',
            'status_effective_date' => 'date',
            'terminated_at' => 'datetime',
            'salary_net' => 'decimal:2',
        ];
    }

    public function nextOfKins(): HasMany
    {
        return $this->hasMany(EmployeeNextOfKin::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(EmployeeCertificate::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(EmployeeStatusHistory::class)->latest('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])));
    }

    public function getEmploymentStatusLabelAttribute(): string
    {
        return ucwords(str_replace('_', ' ', (string) $this->employment_status));
    }
}

