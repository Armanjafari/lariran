<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function ($comment) {
                return [
                    'id' => $comment->id,
                    'desc' => $comment->desc,
                    'score' => $comment->score,
                    'user' => new UserResource($comment->user),
                    'created_at' => $comment->created_at,
                ];
            }),
        ];
    }
}
