<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuanBalaiKerja extends Model
{
    use HasFactory;

    protected $table = 'satuan_balai_kerja';
    protected $fillable = ['nama', 'unor_id'];

    const UPDATED_AT = 'edited_at';
}
