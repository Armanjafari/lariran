<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandLanding extends Model
{
    protected $fillable = [
        'brand_id',
    ];
    use HasFactory;
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
