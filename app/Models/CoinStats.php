<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoinStats extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max4',
        'max6',
        'max12',
        'max24',
        'max48',
        'price'
    ];
}
