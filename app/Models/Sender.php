<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sender extends Model
{
    protected $fillable = [
        'name',
        'address',
        'postal_code',
        'phone_number',
    ];
    use HasFactory;
}
