<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (!is_null($this->image)) {

            return [
                'id' => $this->id,
                'name' => $this->name,
                'persian_name' => $this->persian_name,
                'image' => $this->image->address ?? null,
            ];
        } else {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'persian_name' => $this->persian_name,
                'image' => null,
            ];
        }
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
