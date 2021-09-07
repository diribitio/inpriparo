<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Feedback extends Model
{
    use UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'feedback';
    protected $primaryKey = 'id';
    public $timestamps = true;
}
