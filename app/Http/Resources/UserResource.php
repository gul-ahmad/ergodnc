<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

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

        //Gul here
        //we are doing the work on to not reutrn the folling 
        //fields when user resource is returned
        //by using Arr::except 
        //this is the advantage of using Json Resources
        //we can decide which attributes to show and which to hide
        return 
        [
        $this->merge(Arr::except(parent::toArray($request),[
         'created_at', 'updated_at', 'email', 'email_verified_at'
          ]))


        ];
        
    }
}
