<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductForCategoriesCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function ($variety) {
                return [
                    'id' => $variety->product_id,
                    'title' => $variety->product->title,
                    'persian_title' => $variety->product->persian_title,
                    'slug' => $variety->product->slug,
                    'stock' => $variety->stock,
                    'image' => $variety->product->images->first()->address ?? null,
                    'price' =>  $variety->convertEnglishToPersian($variety->price * $variety->currency->value),
                    'show_price' =>  $variety->convertEnglishToPersian($variety->show_price * $variety->currency->value),
                    'percent' => $variety->percentage(),
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
