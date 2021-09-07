<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Timeframe extends Model
{
    use UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'timeframes';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $casts = [
        'from' => 'datetime',
        'until' => 'datetime',
    ];
}
