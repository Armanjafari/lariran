<?php

namespace App\Http\Resources\v1;

use App\Services\Convert\convertEnglishToPersian;
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
                    'id' => $variety['id'],
                    'title' => $variety['title'],
                    'persian_title' => $variety['persian_title'],
                    'slug' => $variety['slug'],
                    'stock' => $variety['stock'],
                    'image' => $variety['image'] ?? null,
                    'price' =>  convertEnglishToPersian::convertEnglishToPersian($variety['price']),
                    'show_price' => convertEnglishToPersian::convertEnglishToPersian($variety['show_price']),
                    'percent' => $variety['percent'],
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
