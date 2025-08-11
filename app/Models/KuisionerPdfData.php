<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KuisionerPdfData extends Model
{
    use HasFactory;

    protected $fillable = ['material_id', 'peralatan_id', 'tenaga_kerja_id', 'shortlist_id', 'vendor_id'];
}
