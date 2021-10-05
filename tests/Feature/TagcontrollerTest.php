<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagcontrollerTest extends TestCase
{
      use LazilyRefreshDatabase;
     
      /**
     * @test
     */
     public function itListAllTags()
     {

        $response = $this->get('/api/tags');

      /*  dd(
            $response->json()
            
        ); */

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data')[0]['id']);



     }



   

}
