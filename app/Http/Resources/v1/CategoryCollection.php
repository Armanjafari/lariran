<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'data' => $this->collection->transform(function($category){
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'persian_name' => $category->persian_name,
                    'parent_id' => $category->parent_id,
                    'image' => $category->image->address ?? null,
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
