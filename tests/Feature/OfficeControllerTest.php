<?php

namespace Tests\Feature;

use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        Office::factory()->count(3)->create();
        $response = $this->get('/api/offices');
/* 
        dd(
            $response->json()
            
        ); */

       // $response->assertStatus(200)->dump();

        //OR
        $response->assertOk(200)->dump();
        $response->assertJsonCount(3,'data');
        $this->assertNotNull($response->json('data')[0]['id']);
       // $this->assertCount(3,$response->json('data'));
    }
}
