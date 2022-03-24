<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Request;

class ImageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return 
             $this->collection->transform(function ($image) {
                return [
                    'id' => $image->id,
                    'address' => $image->address,
                    'belongs_to' => $image->imageable_id,
                ];
            });
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
