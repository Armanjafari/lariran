<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'persian_title',
        'description',
        'option_id',
        'brand_id',
        'category_id',
        'slug',
        'weight',
        'status',
        'keywords',
    ];
}
