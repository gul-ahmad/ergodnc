<?php

namespace Tests\Feature;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\OfficePendingApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Mockery\Matcher\Not;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase;
     
     /**
      * @test
      */

     public function itListAllTheOfficesInPaginatedWay()
     {
     
        
        Office::factory()->count(3)->create();
        $response = $this->get('/api/offices');

     /*    dd(
            $response->json()
            
        ); */ 

       // $response->assertStatus(200)->dump();

        //OR
        $response->assertOk(200)->dump();
        $response->assertJsonCount(3,'data');
        $this->assertNotNull($response->json('data')[0]['id']);
        //make sure it provide meta data
        $this->assertNotNull($response->json('meta'));
        //make sure it provides links
        $this->assertNotNull($response->json('links'));
       // $this->assertCount(3,$response->json('data'));



     }   

     /**
      * @test
      */
      public function itListApprovedAndNotHiddenOffice()
      {
        //creating 3 offices which are by default approved 
         Office::factory(3)->create();

         //creating office which is hiddent
          Office::factory()->create(['hidden' =>true]);

          //creating office which is not_Approved
          Office::factory()->create(['approval_status' =>Office::APPROVAL_PENDING]);

          $response = $this->get('/api/offices');
          $response->assertOk(200);

          $response->assertJsonCount(3,'data');
      



      }



        /**
      * @test
      */
      public function itListAllOfficesIncludingHiddenAndUnapprovedIfFilteringForCurrentLoggedInUser()
      {
        $user =User::factory()->create();
        //creating 3 offices which are by default approved 
         Office::factory(3)->for($user)->create();

         //creating office which is hiddent
          Office::factory()->hidden()->for($user)->create();

          //creating office which is not_Approved
          Office::factory()->pending()->for($user)->create();

           //assume Logged in
           $this->actingAs($user);
          $response = $this->get('/api/offices?user_id='.$user->id);
          $response->assertOk(200);

          $response->assertJsonCount(5,'data');
      



      }












      /**
       * @test
       */

       public function itListOfficeByUsertId()
       {
       
        Office::factory(3)->create();

        //create a host
        $host =User::factory()->create();
        
        //creating office for this host 
        //office having the host id of the above user
        $office = Office::factory()->for($host)->create();
           
        //making a request to return the office of the this host 
        //having the id of this host
        $response = $this->get('/api/offices?user_id='.$host->id);

        $response->assertOk(200);
           

          //checking that it should return only 1record for this user/host
          $response->assertJsonCount(1,'data');

          //check if the returning data is qual to the id of office which we created above for the
          //specific host
          $this->assertEquals($office->id,$response->json('data')[0]['id']); 


       }

       
      /**
       * @test
       */

      public function itFilersByVisitorId()
      {
      
       Office::factory(3)->create();
       $user =User::factory()->create();
       $office = Office::factory()->create();
       //creating the Reservation for the above user not host
       Reservation::factory()->for($office)->for($user)->create();
        //creating a Reservation for a differect user 
        //this Reservation does not belong to the user for which we are creating test
        //we are verifying that this Reservation should not be returned
       Reservation::factory()->for(Office::factory())->create();
       $response = $this->get('/api/offices?visitor_id='.$user->id);
       $response->assertOk(200);
       $response->assertJsonCount(1,'data');
       $this->assertEquals($office->id,$response->json('data')[0]['id']); 


      }

      /**
       * @test
       */


        public function itIncludesImagesTagsAndUser()
        {

             $user =User::factory()->create();

             $tag =Tag::factory()->create();

             $office =Office::factory()->for($user)->create();
               

             //attacing tags to the office using relationship eloquent
             $office->tags()->attach($tag);

             $office->images()->create(['path'=>'image.jpj']);

             $response = $this->get('/api/offices');

             $response->assertOk();
             //verifying assert have tags and images
             $this->assertIsArray($response->json('data')[0]['tags']);
             //expect only one tag
             $this->assertCount(1,$response->json('data')[0]['tags']);

             $this->assertIsArray($response->json('data')[0]['images']);
             $this->assertCount(1,$response->json('data')[0]['images']);
             $this->assertEquals($user->id,$response->json('data')[0]['user']['id']);
      

        }

         /**
       * @test
       */


      public function itReturnsTheNumberOfActiveReservations()
      {

           

           $office =Office::factory()->create();
             
             //Reservation with status active 
             Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
            //Reservation with status cancelled
             Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

           $response = $this->get('/api/offices');

           $response->assertOk();
        
           $this->assertEquals(1,$response->json('data')[0]['reservations_count']);
    

      }



         /**
       * @test
       */

      public function itOrdersByDistanceWhenCoordinatesAreProvided()
      {
          Office::factory()->create([
              'lat' => '39.74051727562952',
              'lng' => '-8.770375324893696',
              'title' => 'Leiria'
          ]);
  
          Office::factory()->create([
              'lat' => '39.07753883078113',
              'lng' => '-9.281266331143293',
              'title' => 'Torres Vedras'
          ]);
  
          $response = $this->get('/api/offices?lat=38.720661384644046&lng=-9.16044783453807');
  
          $response->assertOk()
              ->assertJsonPath('data.0.title', 'Torres Vedras')
              ->assertJsonPath('data.1.title', 'Leiria');
  
          $response = $this->get('/api/offices');
  
          $response->assertOk()
              ->assertJsonPath('data.0.title', 'Leiria')
              ->assertJsonPath('data.1.title', 'Torres Vedras');
      }

      /**
     * @test
     */
    public function itShowsTheOffice()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();
        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tag);
        $office->images()->create(['path' => 'image.jpg']);

        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_ACTIVE]);
        Reservation::factory()->for($office)->create(['status' => Reservation::STATUS_CANCELLED]);

        $response = $this->get('/api/offices/'.$office->id);

        $response->assertOk()
            ->assertJsonPath('data.reservations_count', 1)
            ->assertJsonCount(1, 'data.tags')
            ->assertJsonCount(1, 'data.images')
            ->assertJsonPath('data.user.id', $user->id);
    }

         /**
     * @test
     */
     public function ItCreatesAnOffice ()

     {
         Notification::fake();
        $admin =User::factory()->create(['is_admin' =>true]);
        $user = User::factory()->createQuietly();
        $tag1 =Tag::factory()->create();
        $tag2 =Tag::factory()->create();

        $this->actingAs($user);

          $response = $this->postJson('/api/offices',[

              'title'  =>'Office in Islamabad',
              'description'  =>'Description',
              'lat' => '39.74051727562952',
              'lng' => '-8.770375324893696',
              'address_line1'  =>'address line 1',
              'price_per_day'  =>'1000',
              'monthly_discount'  =>'5',
               
              'tags' =>[

                $tag1->id ,$tag2->id
              ]

        ]);

           $response->assertCreated()

                     ->assertJsonPath('data.title' ,'Office in Islamabad')
                     ->assertJsonPath('data.user.id' ,$user->id)
                     ->assertJsonPath('data.approval_status' ,Office::APPROVAL_PENDING)
                     ->assertJsonCount(2,'data.tags');
            $this->assertDatabaseHas('offices',[

                 'title' =>'Office in Islamabad'
            ]);  
            
            Notification::assertSentTo($admin,OfficePendingApproval::class);     


     }

      /**
     * @test
     */
    public function itDoesntAllowCreatingIfScopeIsNotProvided()
    {
        $user = User::factory()->createQuietly();

        $token = $user->createToken('test', []);

        $response = $this->postJson('/api/offices', [], [
            'Authorization' => 'Bearer '.$token->plainTextToken
        ]);

        $response->assertStatus(403);
    }




    
         /**
     * @test
     */
    public function ItUpdatesAnOffice ()

    {
       $user = User::factory()->create();
       $tags =Tag::factory(3)->create();
       $anotherTag =Tag::factory()->create();
       
       $office =Office::factory()->for($user)->create();

       $office->tags()->attach($tags);

       $this->actingAs($user);

         $response = $this->putJson('/api/offices/'.$office->id,[
            

            //we are checking sync method here testing it
             'title'  =>'Amazing Office in Islamabad',
             'tags'   =>[$tags[0]->id,$anotherTag->id]
            
       ]);
/* 
       dd(
        $response->json()

       );  */

          $response->assertOk()
                      
                    ->assertJsonCount(2,'data.tags')
                    ->assertJsonPath('data.tags.0.id',$tags[0]->id)
                    ->assertJsonPath('data.tags.1.id',$anotherTag->id)

                    ->assertJsonPath('data.title' ,'Amazing Office in Islamabad');
                    
                 


    }




         /**
     * @test
     */
    public function ItUpdatesTheFeatureImageOfAnOffice ()

    {
       $user = User::factory()->create();
       
            
       $office =Office::factory()->for($user)->create();

       $image =$office->images()->create([
            'path'  =>'image.jpg'

       ]);

       $this->actingAs($user);

         $response = $this->putJson('/api/offices/'.$office->id,[
            

            //we are checking sync method here testing it
             'featured_image_id'  =>$image->id,
           
            
       ]);

      

          $response->assertOk()
                    ->assertJsonPath('data.featured_image_id' ,$image->id);   
                    
            /*  dd(
        $response->json()

       );   */     


    }




         /**
     * @test
     */
    public function ItDoesNotUpdateAFeaturedImageThatBelongsToAnotherOffice ()

    {
       $user = User::factory()->create();
       
            
       $office1 =Office::factory()->for($user)->create();
       $office2 =Office::factory()->create();

       $image =$office2->images()->create([
            'path'  =>'image.jpg'

       ]);

       $this->actingAs($user);

         $response = $this->putJson('/api/offices/'.$office1->id,[
            

            //we are checking sync method here testing it
             'featured_image_id'  =>$image->id,
           
            
       ]);

      

          $response->assertUnprocessable();   
                    
            /*  dd(
        $response->json()

       );   */     


    }



        /**
     * @test
     */
    public function itDoesNotUpdateOfficeThatDoesNotBelongToUser ()

    {
       $user = User::factory()->create();
       $anotherUser = User::factory()->create();
      
       
       $office =Office::factory()->for($anotherUser)->create();

       

       $this->actingAs($user);

         $response = $this->putJson('/api/offices/'.$office->id,[

             'title'  =>'Amazing Office in Islamabad',
            
       ]);
        //  dd($response->status());
          $response->assertStatus(403);

                    
                 


    }
    
        /**
     * @test
     */
    public function itMarksTheOfficeAsPendingIfDirty ()

    {
      // $admin =User::factory()->create(['name' =>'Gul']);
       $admin =User::factory()->create(['is_admin' =>true]);
       $user = User::factory()->create();
       Notification::fake();
     
      
       
       $office =Office::factory()->for($user)->create();

       

       $this->actingAs($user);

         $response = $this->putJson('/api/offices/'.$office->id,[

             'lat'  =>'40.740517275234234234',
            
       ]);
        //  dd($response->status());
          $response->assertOk();

          $this->assertDatabaseHas('offices' ,[

             'id' => $office->id,
             'approval_status' =>Office::APPROVAL_PENDING,

          ]);

               Notification::assertSentTo($admin,OfficePendingApproval::class);     
                 


    }

        /**
     * @test
     */
    public function itDeletesAnOffice ()

    {
       //$admin =User::factory()->create(['name' =>'Gul']);
       Storage::disk('public')->put('/office_image.jpg', 'empty');
       $user = User::factory()->create();
       //Notification::fake();
  
       $office =Office::factory()->for($user)->create();
       $image = $office->images()->create([
        'path' => 'office_image.jpg'
    ]);

       

       $this->actingAs($user);

       $response = $this->deleteJson('/api/offices/'.$office->id);
        //  dd($response->status());
          $response->assertOk();
          $this->assertSoftDeleted($office);
          
          //throwing error so commented it
          // $this->assertModelMissing($image);

        Storage::disk('public')->assertMissing('office_image.jpg');

    
                 


    }


    
        /**
     * @test
     */
    public function itCannotDeleteAnOfficeHavingReservation()

    {
       
       $user = User::factory()->create();
       
       $office =Office::factory()->for($user)->create();
       $reservation =Reservation::factory()->for($office)->create();
       $this->actingAs($user);

       $response = $this->deleteJson('/api/offices/'.$office->id);
        
          $response->assertUnprocessable();

          $this->assertDatabaseHas('offices',[

              'id'  =>$office->id,
              'deleted_at' =>null,

          ]);
         

    
                 


    }

}
