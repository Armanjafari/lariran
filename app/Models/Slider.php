<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'persian_name',
        'type',
        'category_id',
    ];
    public function images()
    {
        return $this->morphOne(Image::class , 'imageable');
    }
}
