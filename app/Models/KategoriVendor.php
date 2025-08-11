<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriVendor extends Model
{
    use HasFactory;

    protected $fillable = ['jenis_vendor_id', 'nama_kategori_vendor'];
}
