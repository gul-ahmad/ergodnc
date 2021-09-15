<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

     //Gul//
     //Resources are used to returl the collection result as Json properly
     //Laravel can by default do it but by using resource we can apply some restrictions on attributes etc
     //Laravel defualt can do do but it make some decision by itself ,so to avoid it we use resource
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
