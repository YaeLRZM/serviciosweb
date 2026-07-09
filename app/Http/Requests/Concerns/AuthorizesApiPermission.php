<?php

namespace App\Http\Requests\Concerns;

trait AuthorizesApiPermission
{
    /**
     * Authorize against Spatie permissions using the JWT api guard.
     */
    protected function allowIfCan(string $permission): bool
    {
        return $this->user('api')?->can($permission) ?? false;
    }
}
