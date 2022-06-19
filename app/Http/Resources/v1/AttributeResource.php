<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => [
                    'id' => $this->id,
                    'name' => $this->name,
                    'category' => new CategoryResource($this->category),
            ],
        ];
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
