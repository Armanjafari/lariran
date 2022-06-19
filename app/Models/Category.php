<?php

namespace App\Models;

use App\Exceptions\FileHasExistsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'persian_name',
        'parent_id',
    ];
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function child()
    {
        return $this->hasMany(Category::class , 'parent_id', 'id');
    }
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
    public function delete()
    {
        $this->load('products');
        if (!$this->products->first()) 
        {
            return parent::delete();
        } else {
            throw new FileHasExistsException('a relation exists');
        }
    }
}
