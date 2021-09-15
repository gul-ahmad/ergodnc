<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

use Illuminate\Http\Request;

class OfficeController extends Controller
{
  public function index()
  {

    $offices =Office::query()
            ->where('approval_status',Office::APPROVAL_APPROVED)
            ->where('hidden',false)
            ->when(request('host_id'),fn ($builder) => $builder->whereUserId(request('host_id')))
            ->when(request('user_id'),
            fn(EloquentBuilder $builder)
             =>$builder->whereRelation('reservations','user_id', '=' , request('user_id')) 
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
}
