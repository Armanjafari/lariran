<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class VarietyResource extends JsonResource
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
            'stock' => $this->stock,
            'price' =>  ($this->price * $this->currency->value),
            'show_price' =>  ($this->show_price * $this->currency->value),
            'waranty_id' =>  new WarantyResource($this->waranty_id),
            // 'product_id' =>  $this->product_id,
            // 'currency_id' =>  $this->currency_id,
            'color_id' =>  new ColorResource($this->color_id),
            // 'is_active' =>  $this->is_active ?? 1,
            // 'product_id' => new ProductResource($this->product),

        ];
    } else {
        return [
            'id' => $this->id,
            'stock' => $this->stock,
            'price' =>  ($this->price * $this->currency->value),
            'show_price' =>  ($this->show_price * $this->currency->value),
            'waranty_id' =>  new WarantyResource($this->waranty_id),
            // 'product_id' =>  $this->product_id,
            // 'currency_id' =>  $this->currency_id,
            'color_id' => null,
            // 'is_active' =>  $this->is_active ?? 1,
            // 'product_id' => new ProductResource($this->product),

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
