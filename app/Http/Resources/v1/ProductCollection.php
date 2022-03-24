<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function($product){
                return [
                    'id' => $this->id,
                    'title' => $product->title,
                    'persian_title' => $product->persian_title,
                    'category_id' => new CategoryResource($product->category),
                    'slug' => $product->slug,
                    'brand_id' => new BrandResource($product->brand),
                    'option_id' => new OptionResource($product->option),
                    'description' => $product->description,
                    'weight' => $product->weight,
                    'keywords' => $product->keywords ?? '',
                    'status' => $product->status ?? '', 
                    'images' => new ImageCollection($product->images), 
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
