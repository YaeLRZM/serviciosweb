<?php

namespace App\Http\Requests\Concerns;

trait AuthorizesApiPermission
{
    protected function allowIfCan(string $permission): bool
    {
        return (bool) $this->user('api')?->hasPermissionTo($permission, 'web');
    }
}
