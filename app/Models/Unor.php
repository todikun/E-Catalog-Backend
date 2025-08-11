<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unor extends Model
{
    use HasFactory;

    protected $table = 'unor';
    protected $fillable = [
        'nama'
    ];
}
