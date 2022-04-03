<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class GradeLevel extends Model
{
    use UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'grade_level';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

}
