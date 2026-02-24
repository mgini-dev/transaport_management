<?php

namespace App\Models\Concerns;

use App\Support\EncryptedId;

trait HasEncryptedId
{
    public function getEncryptedIdAttribute(): string
    {
        return EncryptedId::encode($this->getKey());
    }
}
