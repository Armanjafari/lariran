<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;
    public function isExpired()
    {
        return Carbon::now()->isAfter(Carbon::parse($this->expire_time));
    }
}
