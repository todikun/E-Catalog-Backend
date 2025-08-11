<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peralatan extends Model
{
    use HasFactory;

    protected $table = 'peralatan';
    protected $fillable = [
        'nama_peralatan',
        'satuan',
        'spesifikasi',
        'kapasitas',
        'kodefikasi',
        'kelompok_peralatan',
        'jumlah_kebutuhan',
        'merk',
        'provincies_id',
        'cities_id',
        'identifikasi_kebutuhan_id',
        'satuan_setempat',
        'harga_sewa_satuan_setempat',
        'harga_sewa_konversi',
        'harga_pokok',
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
