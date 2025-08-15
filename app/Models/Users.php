<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable as AuthAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class Users extends Model implements JWTSubject, AuthenticatableContract
{
    use HasFactory, Notifiable, AuthAuthenticatable;


    protected $fillable = ['id_roles', 'nama_lengkap', 'no_handphone', 'nik', 'nrp', 'satuan_kerja_id', 'balai_kerja_id', 'status'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function satuanKerja()
    {
        return $this->belongsTo(SatuanKerja::class, 'satuan_kerja_id');
    }

    public function balaiSatuanKerja()
    {
        return $this->belongsTo(SatuanBalaiKerja::class, 'balai_kerja_id');
    }

     // Get the id from user_id
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getEmailForVerification()
    {
        return $this->email;
    }
}
