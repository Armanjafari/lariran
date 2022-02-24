<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shiping extends Model
{
    use HasFactory;
    protected $fillable = [
        'address',
        'postal_code',
        'city_id',
        'user_id',
    ];
}
