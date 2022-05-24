<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductForOrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return
            $this->collection->transform(function ($variety) { // 'data' =>
                if (!is_null($variety->color_id)) {
                    return [
                        'id' => $variety->id,
                        'price' =>  $variety->pivot->price,
                        'waranty_id' =>  new WarantyResource($variety->waranty),
                        'color_id' =>  new ColorResource($variety->color),
                        // 'image' => $variety->product->first()->address ?? null,
                        'persian_title' => $variety->product->persian_title ?? null,
                        'product_id' => $variety->product->id ?? null,
                        'quantity' => $variety->pivot->quantity
                    ];
                } else {
                    return [
                        'id' => $variety->id,
                        'price' =>  $variety->pivot->price,
                        'waranty_id' =>  new WarantyResource($variety->waranty),
                        'color_id' =>  null,
                        'image' => $variety->product->images->first()->address ?? null,
                        'persian_title' => $variety->product->persian_title,
                        'product_id' => $variety->product->id,
                        'quantity' => $variety->pivot->quantity
                    ];
                }
            });
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
