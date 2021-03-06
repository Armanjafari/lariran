<?php

namespace App\Models;

use App\Exceptions\FileHasExistsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'persian_name',
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function image()
    {
        return $this->morphOne(Image::class , 'imageable');
    }
    public function delete()
    {
        $this->load('products');
        $products = $this->products;
        if ($products->isEmpty()) 
        {
            return parent::delete();
        } else {
            throw new FileHasExistsException('a relation exists');
        }
    }
    public function landing()
    {
        return $this->hasOne(BrandLanding::class);
    }
}
