<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCertificate extends Model
{
    use HasEncryptedId, HasFactory;

    protected $fillable = [
        'employee_id',
        'certificate_name',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getIsPreviewableAttribute(): bool
    {
        return $this->mime_type === 'application/pdf' || str_starts_with((string) $this->mime_type, 'image/');
    }
}

