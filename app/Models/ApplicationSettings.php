<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class ApplicationSettings extends Model
{
    use UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'application_settings';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guard_name = 'api';
}
