<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Event extends Model
{
    use HasRoles, UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'events';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guard_name = 'api';
}
