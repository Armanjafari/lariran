<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OptionCollection extends ResourceCollection
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
            'data' => $this->collection->transform(function($option){
                return [
                    'id' => $option->id,
                    'name' => $option->name,
                ];
            }),
        ];
    }
    public function with($request)
    {
        return [
            'status' => 'success'
        ];
    }
}
