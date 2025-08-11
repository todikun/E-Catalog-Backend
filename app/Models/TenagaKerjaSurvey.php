<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenagaKerjaSurvey extends Model
{
    use HasFactory;

    protected $table = 'tenaga_kerja_survey';
    protected $fillable = [
        'tenaga_kerja_id',
        'harga_per_satuan_setempat',
        'harga_konversi_perjam',
        'keterangan',
    ];
}
