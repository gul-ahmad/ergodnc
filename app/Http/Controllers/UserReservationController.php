<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;

class UserReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->tokenCan('reservations.show')) {
            abort(Response::HTTP_FORBIDDEN);
                 }
            
          //validation
          //better to write validation for status filter so user dont enter any dummy status which does 
          //not exist

           $reservations =Reservation::query()

                 ->where('user_id',auth()->id())
                 ->when(request('office_id'),
                 fn($query) => $query->where('office_id',request('office_id'))
                 
                 )->when(request('status'),
                 fn($query) => $query->where('status',request('status'))
                 
                 )->when(
                     request('from_date') && request('to_date'),
                     function($query) {
                      $query->whereBetween('start_date' ,[request('from_date'),request('end_date')])
                            ->orwhereBetween('end_date' ,[request('from_date'),request('end_date')]);


                     }
                 )
                 ->with(['office.featuredImage'])
                 ->paginate(20);

                 return ReservationResource::collection(
                     $reservations
                    );

    }

}
