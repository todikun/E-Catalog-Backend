<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetugasLapangan extends Model
{
    use HasFactory;
    protected $table = 'petugas_lapangan';
    protected $fillable = [
        'user_id',
        'sk_penugasan'
    ];

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
