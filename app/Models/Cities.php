<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cities extends Model
{
    use HasFactory;
    protected $table = 'cities'; // Specify the table if not following naming conventions

    // Specify the fillable attributes
    protected $fillable = ['provinsi_id', 'kode_kota', 'nama_kota', 'updated_at', 'created_at'];

    public function province()
    {
        return $this->belongsTo(Provinces::class, 'provinsi_id', 'kode_provinsi');
    }

    // A city has many vendors
    public function vendors()
    {
        return $this->hasMany(DataVendor::class, 'kota_id', 'kode_kota');
    }
}
