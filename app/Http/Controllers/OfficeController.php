<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Validators\OfficeValidator;
use App\Notifications\OfficePendingApproval;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;



use Illuminate\Http\Response;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Notification;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
  public function index()
  {

    $offices =Office::query()
           /*  ->where('approval_status',Office::APPROVAL_APPROVED)
            ->where('hidden',false) */

            //applying the check if the current request/filtering is by the user who
            //is logged in show him/her all the offices 

            //Or otherwise apply the check to show only approved and non hidden

            ->when(request('user_id') && auth()->user() && request('user_id')==auth()->id(),
            fn($builder) =>$builder,

            fn($builder) =>$builder->where('approval_status',Office::APPROVAL_APPROVED)
            ->where('hidden',false)
            
            )

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
  
           //we abstracted validation code part and put it in a separted class
           //and call that class method here
             $attributes = ( new OfficeValidator())->validate(
                //creating new instance of office and then passing it to
               //the transction funtion
               $office =new Office(),
               request()->all()


             );

         /*    $attributes =validator(request()->all(),            
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
          
          )->validate(); */

        //  $attributes['user_id'] =auth()->id();
          $attributes['approval_status'] =Office::APPROVAL_PENDING;
          $attributes['user_id'] =auth()->id();

          //we are using DB::transaction here to avoid the conditon as we are bascially performing 2 queries here
          //1-creating office 2-attaching the database
          //we dont want the state where one query fails and other succeeded
          //so to avoid this we use DB:: so either our both queries will either pass or either fail
         $office = DB::transaction(function () use ($office,$attributes) {
             
           /*  $office =auth()->user()->offices()->create( */
                 // $office is created at top where office instance created and passed here
              /* $office =$office->create( */
                //we are not using create here
                //becuase we have created office instance at top $office and we are passing it here
                //so dont use create() we use fill
                
           
              //Tags cannot be inserted ,they can be attached
              //so thats why we are doing in this way
              $office->fill(
              Arr::except($attributes,['tags'])
  
            )->save();
  
            if(isset($attributes['tags']))
            {
              $office->tags()->attach($attributes['tags']);

            }
           
            return $office;

          

          });
         // Notification::send(User::firstWhere('name','Gul'),new OfficePendingApproval($office));
          Notification::send(User::where('is_admin',true)->get(),new OfficePendingApproval($office));
           
         /*  $office =auth()->user()->offices()->create(
           
            //Tags cannot be inserted ,they can be attached
            //so thats why we are doing in this way
            Arr::except($attributes,['tags'])

          );

          $office->tags()->sync($attributes['tags']); */
        


          return OfficeResource::make(
            $office->load(['images','tags','user'])
            );

          }

           //This method is going to use jsonResource thats why we put it there
          public function update(Office $office):JsonResource
          {
           
            if (! auth()->user()->tokenCan('office.update')) {
              abort(Response::HTTP_FORBIDDEN);
                   }

               
              //we are calling here the Policy we make for update
              
              $this->authorize('update' ,$office);
              

  

              $attributes =( new OfficeValidator())->validate($office ,request()->all());

            /* $attributes =validator(request()->all(),            
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
          
          )->validate(); */

                  //fill does not touch the database it only fills the attributes
               $office->fill(Arr::except($attributes,['tags']));
                 //we are checking here if any of following attributes are <touched>
                 //mark the status as Pending
               if($requiresReview = $office->isDirty(['lat','lng','price_per_day'])){
        
                   $office->fill(['approval_status' => Office::APPROVAL_PENDING]);

               }

             DB::transaction(function () use($office ,$attributes) {
                 
                $office->save();
                  //we are using sync here instead of attach()
                  //we can check the difference between sync() and attach() in docs
                  //attch() simply attach the new tags while sync check db and replaces the existing tags with new one provided in the array

                  if(isset($attributes['tags']))
                  {

                    $office->tags()->sync($attributes['tags']);

                  }
                
               }); 

               if($requiresReview){

                Notification::send(User::where('is_admin',true)->get(),new OfficePendingApproval($office));

              }

          return OfficeResource::make(
            $office->load(['images','tags','user'])
            );

          }


          public function delete(Office $office)
          {
 
            if (! auth()->user()->tokenCan('office.delete')) {
              abort(Response::HTTP_FORBIDDEN);
                   }

               
              //we are calling here the Policy we make for update
              
              $this->authorize('delete' ,$office);

              throw_if(
                $office->reservations()->where('status', Reservation::STATUS_ACTIVE)->exists(),
                ValidationException::withMessages(['office' => 'Cannot delete this office!'])
            );

              $office->delete();


          }

          


}
