<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (!is_null($this->option)) {

        return [
            'id' => $this->id,
            'title' => $this->title,
            'persian_title' => $this->persian_title,
            'category_id' => new CategoryResource($this->category),
            'slug' => $this->slug,
            'brand_id' => new BrandResource($this->brand),
            'option_id' => new OptionResource($this->option),
            'description' => $this->description,
            'weight' => $this->weight,
            'keywords' => $this->keywords ?? '',
            'status' => $this->status ?? '', 
            'images' => new ImageCollection($this->images),
            'attributes' => new AttributeValueCollection($this->attributes),
            'varieties' => new VarietyCollection($this->fulls), 
        ];
    } else {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'persian_title' => $this->persian_title,
            'category_id' => new CategoryResource($this->category),
            'slug' => $this->slug,
            'brand_id' => new BrandResource($this->brand),
            'option_id' => null,
            'description' => $this->description,
            'weight' => $this->weight,
            'keywords' => $this->keywords ?? '',
            'status' => $this->status ?? '', 
            'images' => new ImageCollection($this->images),
            'attributes' => new AttributeValueCollection($this->attributes),
            'varieties' => new VarietyCollection($this->fulls), 
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
