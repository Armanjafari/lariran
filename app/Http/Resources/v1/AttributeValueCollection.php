<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AttributeValueCollection extends ResourceCollection
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
             $this->collection->transform(function($attribute_value){
                return [
                    'id' => $attribute_value->id,
                    'value' => $attribute_value->value,
                    'attribute' => new AttributeResource($attribute_value->attribute),
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
