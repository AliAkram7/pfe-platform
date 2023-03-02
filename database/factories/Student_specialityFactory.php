<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class Student_specialityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {   // id 	student_id 	specialty_id 	year_scholar 	created_at 	updated_at
        return [
            'student_id' => $this->faker->numberBetween(1,200) ,
            'speciality_id' => 1 ,
            'year_scholar' => '2022-2023'
        ];
    }
}
