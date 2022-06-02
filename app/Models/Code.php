<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Code extends Model
{
    protected $fillable = [
        'phone_number',
        'code',
        'expired_at',
    ];
    public $timestamps = false;
    use HasFactory;

    public function scopeGenerateCode($query, $user)
    {
        do {
            $code = mt_rand(10000,99999);
        } while($this->checkCodeIsUnique($user , $code));
    }
    private function checkCodeIsUnique($user , int $code)
    {
        return !! $this->user()->activeCode()->whereCode($code)->first();
    }
    public function scopeValidateCode($query , $code)
    {
        return !! $this->whereCode((int)$code)->where('expired_at' , '>' , now())->first();
    }
}
