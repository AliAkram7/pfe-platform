<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class Students_Account_SeederFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' =>  $this->faker->unique()->numberBetween(169,268),
            'name' => $this->faker->name(),
            'default_password' => Str::random(10)  ,
            'specialty_id' => 1 ,
            'year_scholar' =>  2023   ,
        ];
    }
}
