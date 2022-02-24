<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Full extends Model
{
    use HasFactory;
    protected $fillable = [
        'stock',
        'views',
        'price',
        'show_price',
        'waranty_id',
        'color_id',
        'product_id',
        'is_active',
    ];
}
