<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * @param array $options
     * @return void
     */
    public function save(array $options = []): void
    {
        $changes = $this->getDirty();

        /**
         * Update the last_used_at field no more than 1 time per minute.
         * This change increases the performance of HTTP requests requiring sanctum authentication.
         */
        if (
            !array_key_exists('last_used_at', $changes) ||
            count($changes) > 1 ||
            !$this->getOriginal('last_used_at') ||
            $this->getOriginal('last_used_at') < now()->parse($changes['last_used_at'])->subMinute()
        ) {
            parent::save();
        }
    }
}
