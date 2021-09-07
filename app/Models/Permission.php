<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    use UsesTenantConnection;

    protected $hidden = [
        'guard_name',
        'pivot',
    ];
}
