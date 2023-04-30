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
            'student_specialite_id' => $this->faker->numberBetween(157, 211),
            'ms1' => $this->faker->numberBetween(9, 14),
            'ms2' => $this->faker->numberBetween(9, 14),
            'mgc' => $this->faker->numberBetween(9, 14),
            'observation' => $this->faker->numberBetween(1, 4),
        ];
    }
}
