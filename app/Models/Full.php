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
        'currency_id',
    ];
    public function waranty()
    {
        return $this->belongsTo(Waranty::class);
    }
    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function hasStock(int $quantity)
    {
        return $this->stock >= $quantity;
    }
    public function decrementStock($count)
    {
        return $this->decrement('stock' , $count);
    }
}
