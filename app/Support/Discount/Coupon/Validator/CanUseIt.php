<?php
namespace App\Support\Discount\Coupon\Validator;

use App\Models\Coupon;
use App\Exceptions\IllegalCouponException;
use App\Support\Discount\Coupon\Validator\Contracts\AbstractCouponValidator;

class CanUseIt extends AbstractCouponValidator
{
    public function validate(Coupon $coupon)
    {
        // dump($coupon); 
        dd(auth()->user()); 
        if(!auth()->user()->coupons->contains($coupon)){
            throw new IllegalCouponException();
        }

        return parent::validate($coupon);

    }
}