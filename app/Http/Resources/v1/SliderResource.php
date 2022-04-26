<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
                    'id' => $this->id,
                    'persian_name' => $this->persian_name,
                    'link' => $this->link,
                    'type' => $this->type,
                    'color' => $this->color ?? null,
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
