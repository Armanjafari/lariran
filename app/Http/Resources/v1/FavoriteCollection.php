<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FavoriteCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function ($favorite) {
                    return [
                        'id' => $favorite->id,
                        'product' => new ProductResource($favorite),
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
