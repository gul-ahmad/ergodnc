<?php namespace App\Models\Validators;

use App\Models\Office;

use Illuminate\Validation\Rule;

//Gul here
//we added this file here to use for validation of office
//as we are using same validations for office create and update
//so to avoid code repeation we put all validation here 
//and call this in the controller

class OfficeValidator{

        public function validate(Office $office,array $attribues):array
        {
            
            return validator($attribues,            
            [
              
              'title' => [Rule::when($office->exists,'sometimes'),'required','string'],
              'description' => [Rule::when($office->exists,'sometimes'),'required','string'],
              'lat' => [Rule::when($office->exists,'sometimes'),'required','numeric'],
              'lng' => [Rule::when($office->exists,'sometimes'),'required','numeric'],
              'address_line1' => [Rule::when($office->exists,'sometimes'),'required','string'],
              'price_per_day' => [Rule::when($office->exists,'sometimes'),'required','integer' ,'min:100'],

               //checking that image exist and its already belong to an office
               //to become or marked as an featured image
              'featured_image_id' =>[Rule::exists('images','id')->where('resource_type','office')->where('resource_id',$office->id)],
              
              'hidden' => ['bool'],
              'monthly_discount' => ['integer','min:0','max:90'],
              

              'tags' =>['array'],

              'tags.*' =>['integer' ,Rule::exists('tags','id')]

            ]
          
          )->validate();
  

        }




}