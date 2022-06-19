<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class VareityForAdminCollection extends ResourceCollection
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
                if (!is_null($variety->color_id)) {
                    return [
                        'id' => $variety->id,
                        'stock' => $variety->stock,
                        'price' =>  $variety->price,
                        'show_price' =>  $variety->show_price,
                        'converted_price' =>  $variety->convertEnglishToPersian($variety->price * $variety->currency->value),
                        'waranty_id' =>  new WarantyResource($variety->waranty),
                        // 'product_id' =>  $variety->product_id,
                        'currency_id' =>  new CurrencyResource($variety->currency),
                        'color_id' =>  new ColorResource($variety->color),
                        'percent' => $variety->percentage(),
                        'is_active' =>  $variety->is_active ?? null,
                    ];
                } else {
                    return [
                        'id' => $variety->id,
                        'stock' => $variety->stock,
                        'price' =>  $variety->price,
                        'show_price' =>  $variety->show_price,
                        'converted_price' =>  $variety->convertEnglishToPersian($variety->price * $variety->currency->value),
                        'waranty_id' =>  new WarantyResource($variety->waranty),
                        // 'product_id' =>  $variety->product_id,
                        'currency_id' =>  new CurrencyResource($variety->currency),
                        'color_id' =>  null,
                        'percent' => $variety->percentage(),
                        'is_active' =>  $variety->is_active ?? null,
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
