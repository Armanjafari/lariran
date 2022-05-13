<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'amount',
        'shiping_id',
    ];

    use HasFactory;

    // TODO fix this thing
    public function fulls()
    {
        return $this->belongsToMany(Full::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function generateInvoice()
    {
        $pdf = \PDF::loadView('order.invoice', ['order' => $this]);
    }
    

    public function shiping()
    {
        return $this->belongsTo(Shiping::class);
    }
}
