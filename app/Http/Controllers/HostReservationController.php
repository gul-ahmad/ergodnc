<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;

class HostReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_unless(auth()->user()->tokenCan('reservations.show'),
        Response::HTTP_FORBIDDEN
    );

    validator(request()->all(), [
        'status' => [Rule::in([Reservation::STATUS_ACTIVE, Reservation::STATUS_CANCELLED])],
        'office_id' => ['integer'],
        'user_id' => ['integer'],
        'from_date' => ['date', 'required_with:to_date'],
        'to_date' => ['date', 'required_with:from_date', 'after:from_date'],
    ])->validate();

    $reservations = Reservation::query()
        ->whereRelation('office','user_id','=', auth()->id())
        ->when(request('office_id'),
            fn($query) => $query->where('office_id', request('office_id'))
        )->when(request('user_id'),
        fn($query) => $query->where('user_id', request('user_id'))
        )->when(request('status'),
            fn($query) => $query->where('status', request('status'))
        )->when(request('from_date') && request('to_date'),
            function ($query) {
                $query->where(function ($query) {
                    return $query->whereBetween('start_date', [request('from_date'), request('to_date')])
                        ->orWhereBetween('end_date', [request('from_date'), request('to_date')]);
                });
            }
        )
        ->with(['office.featuredImage'])
        ->paginate(20);

    return ReservationResource::collection(
        $reservations
    );     
     
  //->whereRelation('office','user_id','=', auth()->id())
  //added by Gul
  //fitlering offices that belongs to the user //Reservations made Or that belong to my office

    }
            /* public function practice()
            {

             abort_unless(auth()->user()->tokenCan('reservations.show'),
             RESPONSE::HTTP_FORBIDDEN);

              validator(request()->all(),[
               'status' => [Rule::in([Reservation::STATUS_ACTIVE,Reservation::STATUS_CANCELLED])],
               'user_id'=>['interger'],
               'office_id'=>['interger'],


               ])->validate();

             $reservations1 =Reservation::query()
                            ->whereRelation('office','user_id','=',auth()->user())
                            ->when(request('office_id'),
                             fn($query) =>$query->where('office_id',request('office_id'))
                            )->when(request('user_id'),
                            fn($query)=>$query->where('user_id',request('user_id'))
                            )->when(request('from_date') && request('to_date'),
                            function($query) {
                               $query->where(function($query){
                                     
                                return $query->whereBetween('start_date',[request('from_date'),request('to_date')])
                                             ->orWhereBetween('from_date',[request('from_date'),request('to_date')]);  

                               });

                            }        
                            )->when(request('status'),
                            fn($query) =>$query->where('status',request('status')),
                            )->paginate();
                            
                            
                            


                
            } */

   
}
