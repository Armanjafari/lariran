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
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function option()
    {
        return $this->belongsTo(Option::class);
    }
    public function fulls()
    {
        return $this->hasMany(Full::class);
    }
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function attributes()
    {
        return $this->hasMany(AttributeValue::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
