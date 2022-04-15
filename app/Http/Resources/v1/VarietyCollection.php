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
            'data' => $this->collection->transform(function($varierty){
                return [
                    'id' => $varierty->id,
                    // 'stock' => $varierty->stock,
                    'price' =>  $varierty->price,
                    'show_price' =>  $varierty->show_price,
                    'waranty_id' =>  new WarantyResource($varierty->waranty),
                    // 'product_id' =>  $varierty->product_id,
                    // 'currency_id' =>  $varierty->currency_id,
                    'color_id' =>  $varierty->color_id ?? null,
                    // 'is_active' =>  $varierty->is_active ?? 1,
                    // 'product_id' => new ProductResource($varierty->product),
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
