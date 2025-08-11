<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanDataList extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaan_data_list';
    protected $fillable = [
        'section',
        'item_number',
        'description',
    ];
}
