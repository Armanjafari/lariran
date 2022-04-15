<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VarietyCollection extends ResourceCollection
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
            $this->collection->transform(function ($variety) { // 'data' =>
                if (!is_null($variety->color_id)) {
                    return [
                        'id' => $variety->id,
                        // 'stock' => $variety->stock,
                        'price' =>  ($variety->price * $variety->currency->value),
                        'show_price' =>  ($variety->show_price * $variety->currency->value),
                        'waranty_id' =>  new WarantyResource($variety->waranty),
                        // 'product_id' =>  $variety->product_id,
                        // 'currency_id' =>  $variety->currency_id,
                        'color_id' =>  new ColorResource($variety->color_id),
                        // 'is_active' =>  $variety->is_active ?? 1,
                        // 'product_id' => new ProductResource($variety->product),
                    ];
                } else {
                    return [
                        'id' => $variety->id,
                        // 'stock' => $variety->stock,
                        'price' =>  ($variety->price * $variety->currency->value),
                        'show_price' =>  ($variety->show_price * $variety->currency->value),
                        'waranty_id' =>  new WarantyResource($variety->waranty),
                        // 'product_id' =>  $variety->product_id,
                        // 'currency_id' =>  $variety->currency_id,
                        'color_id' =>  null,
                        // 'is_active' =>  $variety->is_active ?? 1,
                        // 'product_id' => new ProductResource($variety->product),
                    ];
                }
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
