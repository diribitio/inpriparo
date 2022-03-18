<?php

namespace App\Models;

use App\Notifications\ProjectDeletedNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles, Notifiable, UsesTenantConnection;

    protected $connection='tenant';
    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guard_name = 'api';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendProjectDeletedNotification()
    {
        $this->notify(new ProjectDeletedNotification());
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function offered_friendships()
    {
        return $this->hasMany(Friendship::class, 'applicant_id');
    }

    public function received_friendships()
    {
        return $this->hasMany(Friendship::class, 'respondent_id');
    }

    public function preferences()
    {
        return $this->hasMany(Preference::class, 'user_id');
    }
}
