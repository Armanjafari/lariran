<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SliderCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function($slider){
                return [
                    'id' => $slider->id,
                    'persian_name' => $slider->persian_name,
                    'link' => $slider->link,
                    'type' => $slider->type,
                    'color' => $slider->color ?? null,
                    'image' => $slider->image->address ?? null,
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
