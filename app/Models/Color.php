<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    use HasFactory;
    protected $fillable = [
        'title' ,
        'value',
        'option_id',
    ];
    public function option()
    {
        $this->belongsTo(Option::class);
    }
}
