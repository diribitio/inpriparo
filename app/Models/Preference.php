<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Preference extends Model
{
    use UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'preferences';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
