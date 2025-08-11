<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerencanaanData extends Model
{
    use HasFactory;

    protected $table = 'perencanaan_data';
    protected $fillable = [
        'informasi_umum_id',
        'identifikasi_kebutuhan_id',
        'shortlist_vendor_id',
        'status',
        'petugas_lapangan_id',
        'pengawas_id',
        'pengolah_data_id',
        'doc_berita_acara',
        'doc_berita_acara_validasi'
    ];

    public function informasiUmum()
    {
        return $this->belongsTo(InformasiUmum::class, 'informasi_umum_id', 'id');
    }

    public function shortlistVendor()
    {
        return $this->hasMany(ShortlistVendor::class, 'shortlist_vendor_id', 'shortlist_vendor_id');
    }

    public function material()
    {
        return $this->hasMany(Material::class, 'identifikasi_kebutuhan_id', 'identifikasi_kebutuhan_id');
    }

    public function peralatan()
    {
        return $this->hasMany(Peralatan::class, 'identifikasi_kebutuhan_id', 'identifikasi_kebutuhan_id');
    }

    public function tenagaKerja()
    {
        return $this->hasMany(TenagaKerja::class, 'identifikasi_kebutuhan_id', 'identifikasi_kebutuhan_id');
    }
}
