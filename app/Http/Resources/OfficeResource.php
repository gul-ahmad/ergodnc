<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class OfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //Gul here
        //instead of returning User as a Model
        //we want to return it as resource
        //any change in UserResource will be reflected here as well
        
        return [
        
           'user' => UserResource::make($this->user),
           'images' => ImageResource::collection($this->images),
           'tags' => TagResource::collection($this->tags),

           $this->merge(Arr::except(parent::toArray($request),[
            'user_id','created_at','updated_at','deleted_at'
  
          ]))
        ];
        
        
        
        //The Arr::except method removes the given key / value 
        //pairs from an array:
      //  return Arr::except(parent::toArray($request),[
        //  'user_id','created_at','updated_at','deleted_at'

        //]);
    }
}
