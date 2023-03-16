<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'member_1' =>  $this->faker->unique()->numberBetween(425,525),
            'member_2' =>  $this->faker->unique()->numberBetween(425,525),
            // 'supervisor_id' =>  $this->faker->unique()->numberBetween(426,525),
        ];
    }
}
