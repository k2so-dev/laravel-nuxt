<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Update the last_used_at field no more than 1 time per minute.
     * This change increases the performance of HTTP requests requiring sanctum authentication.
     */
    protected function lastUsedAt(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => $this->getOriginal('last_used_at') < now()->parse($value)->subMinute()
                ? $value
                : $this->getOriginal('last_used_at'),
        );
    }
}
