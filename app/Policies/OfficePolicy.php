<?php

namespace App\Policies;

use App\Models\Office;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfficePolicy
{
    use HandlesAuthorization;
       //Gul here
       //we are making this update policy to check that user is updating his own created office
      public function update(User $user ,Office $office)
      {

       return $user->id == $office->user_id;



      }
      public function delete(User $user ,Office $office)
      {

       return $user->id == $office->user_id;



      }
}
