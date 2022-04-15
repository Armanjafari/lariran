<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BasketCollection extends ResourceCollection
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
             $this->collection->transform(function($varierty){
                return [
                    'id' => $varierty->id,
                    'price' => $varierty->price,
                    'show_price' => $varierty->show_price,
                    'persian_title' => $varierty->product->persian_title,
                    'title' => $varierty->product->persian_title,
                    'price' => $varierty->price,
                    'color_id' => $varierty->color_id ?? null,
                    'quantity' => $varierty->quantity,
                    'image' => $varierty->product->images->first() ?? '',

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
