<?php

namespace App\Http\Resources\v1;

use App\Models\Category;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductByCategoriesCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function ($product) {
                // return $product['attributes'];
                return [
                    'id' => $product['id'],
                    'title' => $product['title'],
                    'persian_title' => $product['persian_title'],
                    'category_id' => $product['category_id'],
                    'slug' => $product['slug'],
                    'brand_id' => $product['brand_id'],
                    'option_id' =>  $product['option_id'],
                    'description' => $product['description'],
                    'weight' => $product['weight'],
                    'show_weight' => $this['show_weight'] ?? '',
                    'keywords' => $product['keywords'] ?? '',
                    'status' => $product['status'] ?? '',
                    'stock' => $product['stock'],
                    'images' => new ImageCollection($product['image']),
                    // 'images' => $product['image'],
                    // 'attributes' => $product['attributes'],
                    'varieties' => $product['varieties'],

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
