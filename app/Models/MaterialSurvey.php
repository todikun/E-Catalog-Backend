<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialSurvey extends Model
{
    use HasFactory;

    protected $table = 'material_survey';
    protected $fillable = [
        'material_id',
        'satuan_setempat',
        'satuan_setempat_panjang',
        'satuan_setempat_lebar',
        'satuan_setempat_tinggi',
        'harga_satuan_setempat',
        'harga_konversi_satuan_setempat',
        'harga_khusus',
        'keterangan',
    ];
}
