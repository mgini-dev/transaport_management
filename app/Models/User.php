<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'email', 'email');
    }

    public function getEmployeePhotoUrlAttribute(): ?string
    {
        $normalizedEmail = strtolower(trim((string) $this->email));
        if ($normalizedEmail === '') {
            return null;
        }

        $employee = Employee::query()
            ->select(['id', 'photo_path'])
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if (! $employee || ! filled($employee->photo_path)) {
            return null;
        }

        if (! Storage::disk('private')->exists((string) $employee->photo_path)) {
            return null;
        }

        return route('profile.photo');
    }
}
