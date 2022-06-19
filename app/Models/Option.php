<?php

namespace App\Models;

use App\Exceptions\FileHasExistsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];
    public function values()
    {
        return $this->hasMany(Color::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function delete()
    {
        $this->load('products' , 'values');
        if (!$this->products->first()) 
        {
            if (!$this->values->first()) 
            return parent::delete();
        } else {
            throw new FileHasExistsException('a relation exists');
        }
    }
}
