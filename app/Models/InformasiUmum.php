<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformasiUmum extends Model
{
    use HasFactory;

    protected $table = 'informasi_umum';
    protected $fillable = ['kode_rup', 'nama_paket', 'nama_ppk', 'jabatan_ppk', 'nama_balai', 'identifikasi_id', 'jenis_informasi'];

    public function perencanaanData()
    {
        return $this->hasMany(PerencanaanData::class, 'informasi_umum_id', 'id');
    }
}
