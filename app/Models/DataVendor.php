<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataVendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_vendor',
        'alamat',
        'no_telepon',
        'no_hp',
        'nama_pic',
        'provinsi_id',
        'kota_id',
        'koordinat',
        'logo_url',
        'dok_pendukung_url',
        'sumber_daya'
    ];

    protected $casts = [
        'jenis_vendor_id' => 'array',
        'kategori_vendor_id' => 'array',
    ];

    public function provinces()
    {
        return $this->belongsTo(Provinces::class, 'provinsi_id', 'kode_provinsi');
    }

    public function cities()
    {
        return $this->belongsTo(Cities::class, 'kota_id', 'kode_kota');
    }

    public function shortlist_vendor()
    {
        return $this->hasMany(ShortlistVendor::class, 'data_vendor_id', 'id');
    }

    public function kategori_vendor()
    {
        return $this->belongsTo(KategoriVendor::class, 'kategori_vendors_id', 'id');
    }

    public function sumber_daya_vendor(){
        return $this->hasMany(SumberDayaVendor::class,'data_vendor_id','id');
    }
}
