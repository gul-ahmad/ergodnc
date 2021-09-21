<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Office::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'address_line1' => $this->faker->address,
            'approval_status' => Office::APPROVAL_APPROVED,
            'hidden' => false,
            'price_per_day' => $this->faker->numberBetween(1_000, 2_000),
            'monthly_discount' => 0
        ];
    }
    public function pending()
    {

      return $this->state([
         
        'approval_status' =>Office::APPROVAL_PENDING,

      ]);


    }
   

    //Gul here
    //we removed this status 
    //so commented here no needed further
   /*  public function rejected()
    {

      return $this->state([
         
        'approval_status' =>Office::APPROVAL_REJECTED,

      ]);


    } */

      public function hidden()
      {

         return $this->state([

           'hidden' =>true,

         ]);



      }





}
