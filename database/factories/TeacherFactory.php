<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->randomNumber(8,true),
            'name'=>$this->faker->name(),
            'personal_email' => $this->faker->unique()->safeEmail(),
            'institutional_email' => $this->faker->unique()->safeEmail(),
            'tel' => $this->faker->phoneNumber(),
            'grade_id' =>$this->faker->numberBetween(1,5),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ];
    }
}
