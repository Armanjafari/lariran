<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function($order){
                return [
                    'id' => $order->id,
                    'amount_without_shipping_price' => $order->amount,
                    'user' => new UserResource($order->user),
                    'gateway' => $order->payment->gateway ?? null,
                    'tracking_code' => $order->payment->trackingCode ?? null,
                    'amount' => $order->payment->amount,
                    'shiping' => new ShippingResource($order->shiping),
                    'ref_num' => $order->payment->ref_num,
                    'status' => [
                        'id' => $order->payment->status,
                        'name' => __('orders.' . $order->payment->status)],
                    'products' => new ProductForOrderCollection($order->fulls),
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
