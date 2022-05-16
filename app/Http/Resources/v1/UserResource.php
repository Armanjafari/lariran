<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "id" => 1,
            "name" => $this->name ?? null,
            "email" => $this->email ?? null,
            "phone_number" => $this->phone_number ?? null,
            // "roles"=> [
            //         "id"=> $this->roles->id ?? null,
            //         "name"=> $this->roles->name ?? null,
            //         "persian_name"=> $this->roles->persian_name ?? null,]
        ]; // TODO fix this shit (roles)
    }
}
