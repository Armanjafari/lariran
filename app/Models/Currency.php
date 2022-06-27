<?php

namespace App\Models;

use App\Exceptions\FileHasExistsException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'persian_name',
        'value',
    ];
    public function products()
    {
        return $this->hasMany(Full::class);
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
}
