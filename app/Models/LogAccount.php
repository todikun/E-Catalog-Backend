<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAccount extends Model
{
    use HasFactory;

    protected $table = 'log_account';
    protected $fillable = [
        'user_id',
        'action',
        'ip_address'
    ];
}
