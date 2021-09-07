<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Timeframe;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Project extends Model
{
    use UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'projects';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $with = ['timeframes'];

    protected $casts = [
        'authorized' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function participants()
    {
        return $this->hasMany(User::class)->where('project_role', 1);
    }

    public function assistants()
    {
        return $this->hasMany(User::class)->where('project_role', 2);
    }

    public function leader()
    {
        return $this->hasMany(User::class)->where('project_role', 3);
    }

    public function timeframes()
    {
        return $this->hasMany(Timeframe::class);
    }
}
