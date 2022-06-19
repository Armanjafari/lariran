<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BrandForLandingPageCollection extends ResourceCollection
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

                return [
                    'id' => $brand->id,
                    'brand' => new BrandResource($brand->brand),
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
