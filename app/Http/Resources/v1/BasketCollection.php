<?php

namespace App\Http\Resources\v1;

use App\Services\Convert\convertEnglishToPersian;
use App\Support\Cost\Contracts\CostInterface;
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
        $cost = resolve(CostInterface::class);
        return [
            $this->collection->transform(function ($varierty) {
                try {
                    return [
                        'id' => $varierty->id,
                        'price' => $varierty->convertEnglishToPersian($varierty->price * $varierty->currency->value),
                        'show_price' => $varierty->convertEnglishToPersian($varierty->show_price * $varierty->currency->value),
                        'persian_title' => $varierty->product->persian_title,
                        'title' => $varierty->product->persian_title,
                        'color_id' => new ColorResource($varierty->color),
                        'quantity' => $varierty->quantity,
                        'product_id' => $varierty->product_id,
                        'slug' => $varierty->product->slug,
                        'image' => $varierty->product->images->first() ?? '',
                        'percent' => $varierty->percentage(),
                        
                    ];
                } catch (\Throwable $th) {
                    return [
                        'id' => $varierty->id,
                        'price' => $varierty->convertEnglishToPersian($varierty->price * $varierty->currency->value),
                        'show_price' => $varierty->convertEnglishToPersian($varierty->show_price * $varierty->currency->value),
                        'persian_title' => $varierty->product->persian_title,
                        'title' => $varierty->product->persian_title,
                        'color_id' => $varierty->color_id ?? null,
                        'quantity' => $varierty->quantity,
                        'product_id' => $varierty->product_id,
                        'slug' => $varierty->product->slug,
                        'image' => $varierty->product->images->first() ?? '',
                        'percent' => $varierty->percentage(),
                        
                    ];
                }
                
            }),
            'total' => convertEnglishToPersian::convertEnglishToPersian($cost->getTotalCosts()),
        ];
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
