<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    protected $fillable = [
        'link',
        'type',
        'persian_name',
    ];
    public function image()
    {
        return $this->morphOne(Image::class , 'imageable');
    }
}
