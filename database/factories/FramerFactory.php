<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FramerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'teacher_id' => $this->faker->numberBetween(3, 12) ,
            'specialty_id' => 6,
            'number_team_accepted' => 2 ,
        ];
    }
}
