<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class Teacher_account_seedersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->randomNumber(8, true),
            'name' => $this->faker->name(),
            'institutional_email' => $this->faker->unique()->safeEmail(),
            'grade' => $this->faker->numberBetween(1, 5),
        ];
    }
}
