<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengawas extends Model
{
    use HasFactory;
    protected $table = 'pengawas';
    protected $fillable = [
        'user_id',
        'sk_penugasan'
    ];

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
