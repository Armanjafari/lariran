<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount_without_shipping_price' => $this->amount,
            'user' => new UserResource($this->user),
            'gateway' => $this->payment->gateway ?? null,
            'tracking_code' => $this->payment->trackingCode ?? null,
            'amount' => $this->payment->amount,
            'shiping' => new ShippingResource($this->shiping),
            'ref_num' => $this->payment->ref_num,
            'created_at' => $this->created_at,
            'status' => [
                'id' => $this->payment->status,
                'name' => __('orders.' . $this->payment->status)],
            'products' => new ProductForOrderCollection($this->fulls),
        ];
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
