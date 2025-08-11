<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenagaKerja extends Model
{
    use HasFactory;

    protected $table = 'tenaga_kerja';
    protected $fillable = [
        'jenis_tenaga_kerja',
        'satuan',
        'jumlah_kebutuhan',
        'kodefikasi',
        'provincies_id',
        'cities_id',
        'identifikasi_kebutuhan_id',
        'harga_per_satuan_setempat',
        'harga_konversi_perjam',
        'keterangan',
    ];

    public function perencanaanData()
    {
        return $this->belongsTo(PerencanaanData::class, 'identifikasi_kebutuhan_id', 'identifikasi_kebutuhan_id');
    }

    public function  shortlist_vendor()
    {
        return $this->belongsTo(ShortlistVendor::class, 'identifikasi_kebutuhan_id', 'shortlist_vendor_id');
    }

    public function provinces()
    {
        return $this->belongsTo(Provinces::class, 'provincies_id', 'kode_provinsi');
    }

    public function cities()
    {
        return $this->belongsTo(Cities::class, 'cities_id', 'kode_kota');
    }
}
