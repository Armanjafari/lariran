<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class torobPrdocutsCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($vareity) {
            if ((int)$vareity->stock) {
                return [
                    'title' => $vareity->product->persian_title,
                    'subtitle' => $vareity->product->title,
                    'page_unique' => $vareity->product->id,
                    'current_price' => $vareity->price * $vareity->currency->value,
                    'old_price' => $vareity->show_price * $vareity->currency->value,
                    'availability' => 'instock',
                    'category_name' => $vareity->product->category->name,
                    'image_link' => 'https://api.lariran.com/' . $vareity->product->images->first()->address ?? '',
                    'page_url' => 'https://lariran.com/product/' . $vareity->product->id . '/' . $vareity->product->slug,
                    'guarantee' => $vareity->waranty->name,
                    

                ];
            }else {
                return [
                    'title' => $vareity->product->persian_title,
                    'subtitle' => $vareity->product->title,
                    'page_unique' => $vareity->product->id,
                    'current_price' => $vareity->price * $vareity->currency->value,
                    'old_price' => $vareity->show_price * $vareity->currency->value,
                    'availability' => 'outofstock',
                    'category_name' => $vareity->product->category->name,
                    'image_link' => 'https://api.lariran.com/' . $vareity->product->images->first()->address ?? '',
                    'page_url' => 'https://lariran.com/product/' . $vareity->product->id . '/' . $vareity->product->slug,
                    'guarantee' => $vareity->waranty->name,
                    

                ];
            }
                
            });
    }
}
