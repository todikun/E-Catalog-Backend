<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengolahData extends Model
{
    use HasFactory;
    protected $table = 'pengolah_data';
    protected $fillable = [
        'user_id',
        'sk_penugasan'
    ];

    public function user()
    {
        return $this->belongsTo(Users::class);
    }
}
