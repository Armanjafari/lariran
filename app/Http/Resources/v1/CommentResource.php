<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

class CommentResource extends JsonResource
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
            'desc' => $this->desc,
            'score' => $this->score,
            'user' => new UserResource($this->user),
            'created_at' => Jalalian::forge($this->created_at)->format('%A, %d %B %y'),
        ];
    }
    public function with($request)
    {
        return [
            'status' => 'success',
        ];
    }
}
