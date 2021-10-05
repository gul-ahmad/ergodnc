<?php

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;


    //Gul here
    //we are over riding the defualt acting as method
    //and assigning all the abilities to user
    //abilities work in the case of token based auth but not in session based
    //so Muhammad said overwrtie it to avoid writig abitliest again and again
    public function actingAs(Authenticatable $user, $abilites = ['*'])
    {
      Sanctum::actingAs($user,$abilites)  ;
    }
}
