<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BrandCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function ($brand) {
                if (!is_null($brand->image)) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'persian_name' => $brand->persian_name,
                        'image' => $brand->image->address ?? null,
                    ];
                } else {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'persian_name' => $brand->persian_name,
                        'image' => null,
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
