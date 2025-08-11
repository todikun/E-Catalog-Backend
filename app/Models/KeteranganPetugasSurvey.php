<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeteranganPetugasSurvey extends Model
{
    use HasFactory;

    protected $table = 'keterangan_petugas_survey';
    protected $fillable = [
        'petugas_lapangan_id',
        'pengawas_id',
        'tanggal_survey',
        'tanggal_pengawasan',
        'nama_pemberi_informasi',
        'identifikasi_kebutuhan_id',
    ];
}
