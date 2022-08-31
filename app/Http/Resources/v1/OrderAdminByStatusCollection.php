<?php

namespace App\Http\Resources\v1;

use App\Services\Convert\convertEnglishToPersian;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Morilog\Jalali\Jalalian;

class OrderAdminByStatusCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function($payment){
                return [
                    'id' => $payment->order->id,
                    'amount_without_shipping_price' => convertEnglishToPersian::convertEnglishToPersian($payment->order->amount),
                    'user' => new UserResource($payment->order->user),
                    'gateway' => $payment->gateway ?? null,
                    'tracking_code' => $payment->trackingCode ?? null,
                    'amount' => convertEnglishToPersian::convertEnglishToPersian($payment->amount),
                    'shiping' => new ShippingResource($payment->order->shiping),
                    'ref_num' => $payment->ref_num,
                    'created_at' => Jalalian::forge($payment->order->created_at)->format('%A, %d %B %y'),
                    'status' => [
                        'id' => $payment->status,
                        'name' => __('orders.' . $payment->status)],
                    'products' => new ProductForOrderCollection($payment->order->fulls),
                ];
            }),
        ];
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
