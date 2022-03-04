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
    public function waranty()
    {
        $this->belongsTo(Waranty::class);
    }
    public function color()
    {
        $this->belongsTo(Color::class);
    }
    public function product()
    {
        $this->belongsTo(Product::class);
    }
}
