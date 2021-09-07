<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Friendship extends Model
{
    use UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'friendship';
    protected $primaryKey = 'id';
    public $timestamps = true;

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function respondent()
    {
        return $this->belongsTo(User::class, 'respondent_id');
    }
}
