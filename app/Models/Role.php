<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    use UsesTenantConnection;
}
