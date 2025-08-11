<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerifikasiValidasi extends Model
{
    use HasFactory;
    protected $table = 'verifikasi_validasi';
    protected $fillable = [
        'data_vendor_id',
        'shortlist_vendor_id',
        'item_number',
        'status_pemeriksaan',
        'verified_by',
    ];
}
