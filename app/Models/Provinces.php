<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinces extends Model
{
    use HasFactory;

    protected $fillable = ['kode_provinsi', 'nama_provinsi'];

    public function vendors()
    {
        return $this->hasMany(DataVendor::class, 'provinsi_id', 'kode_provinsi');
    }

    public function cities()
    {
        return $this->hasMany(Cities::class, 'provinsi_id', 'kode_provinsi');
    }
}
