<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // student_id 	specialty_id 	year_of_study 	ms1 	ms2 	mgc 	observation
              'student_specialite_id' => $this->faker->unique()->numberBetween(101,105),
            'ms1' => $this->faker->numberBetween(9,20) ,
            'ms2' => $this->faker->numberBetween(9,20) ,
            'mgc' => $this->faker->numberBetween(9,20) ,
            'observation' => $this->faker->numberBetween(1,3),
        ];
    }
}
