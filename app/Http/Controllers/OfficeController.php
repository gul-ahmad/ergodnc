<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;


use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule as ValidationRule;

class OfficeController extends Controller
{
  public function index()
  {

    $offices =Office::query()
            ->where('approval_status',Office::APPROVAL_APPROVED)
            ->where('hidden',false)
            ->when(request('user_id'),fn ($builder) => $builder->whereUserId(request('user_id')))
            ->when(request('visitor_id'),
            fn(EloquentBuilder $builder)
             =>$builder->whereRelation('reservations','user_id', '=' , request('visitor_id')) 
             )
            //->latest('id')
            ->when(
              request('lat') && request('lng'),
              fn ($builder) =>$builder->NearestTo(request('lat'),request('lng')),
              fn ($builder) => $builder->OrderBy('id','ASC')
              
              
              
              )
            ->with(['images' ,'tags' ,'user'])
            ->withCount(['reservations' =>fn($builder)=>$builder->where('status',Reservation::STATUS_ACTIVE)])
            ->paginate(20);
 

            return OfficeResource::collection(

                $offices
            );


  }

            public function show(Office $office)
            {

              $office->loadCount(['reservations' => fn($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
                     ->load(['images', 'tags', 'user']);

             return OfficeResource::make($office) ;
            }

          public function create() :JsonResource
          {
            if (! auth()->user()->tokenCan('office.create')) {
              abort(Response::HTTP_FORBIDDEN);
          }
  

            $attributes =validator(request()->all(),            
            [
              
              'title' => ['required','string'],
              'description' => ['required','string'],
              'lat' => ['required','numeric'],
              'lng' => ['required','numeric'],
              'address_line1' => ['required','string'],
              'hidden' => ['bool'],
              'price_per_day' => ['required','integer' ,'min:100'],
              'monthly_discount' => ['integer','min:0','max:90'],

              'tags' =>['array'],

              'tags.*' =>['integer' ,Rule::exists('tags','id')]

            ]
          
          )->validate();

        //  $attributes['user_id'] =auth()->id();
          $attributes['approval_status'] =Office::APPROVAL_PENDING;
           
          $office =auth()->user()->offices()->create(
           
            //Tags cannot be inserted ,they can be attached
            //so thats why we are doing in this way
            Arr::except($attributes,['tags'])

          );

          $office->tags()->sync($attributes['tags']);

          return OfficeResource::make($office);

          }


}
