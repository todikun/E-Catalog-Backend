<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class Accounts extends Model implements JWTSubject, AuthenticatableContract
{
    use HasFactory;
    use Authenticatable;
    use Notifiable;

    protected $fillable = ['user_id', 'username', 'password', 'remember_token'];

    protected $hidden = [
        'password'
    ];

    public function getJWTIdentifier()
    {
        return $this->user_id;
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getEmailForVerification()
    {
        return $this->email;
    }

    public function user(){
        return $this->belongsTo(Users::class,'user_id');
    }
}
