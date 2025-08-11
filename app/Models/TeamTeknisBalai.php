<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamTeknisBalai extends Model
{
    use HasFactory;
    protected $table = 'team_teknis_balai';
    protected $fillable = [
        'nama_team',
        'user_id_ketua',
        'user_id_sekretaris',
        'url_sk_penugasan'
    ];

    protected $casts = [
        'user_id_anggota' => 'array',
    ];
}
