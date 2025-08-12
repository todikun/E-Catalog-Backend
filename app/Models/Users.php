<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Users extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

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

    public function account()
    {
        return $this->hasOne(Accounts::class, 'user_id', 'id');
    }
}
