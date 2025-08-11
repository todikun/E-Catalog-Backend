<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeralatanSurvey extends Model
{
    use HasFactory;

    protected $table = 'peralatan_survey';
    protected $fillable = [
        'peralatan_id',
        'satuan_setempat',
        'harga_sewa_satuan_setempat',
        'harga_sewa_konversi',
        'harga_pokok',
        'keterangan',
    ];
}
