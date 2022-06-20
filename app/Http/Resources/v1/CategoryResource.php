<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'persian_name' => $this->persian_name,
            'parent_id' => $this->parent_id,
            'image' => $this->image->address ?? null,
        ];
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
