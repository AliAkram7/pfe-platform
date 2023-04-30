<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        // \App\Models\Student::factory(10)->create();
        \App\Models\Rank::factory(100)->create();
        // \App\Models\Students_Account_Seeder::factory(25)->create() ;
        // \App\Models\Student::factory(100)->create();
        // \App\Models\Theme::factory(10)->create();
        // \App\Models\Framer::factory(12)->create() ;
        // \App\Models\Teacher_account_seeders::factory(12)->create() ;
        // \App\Models\Student_speciality::factory(100)->create();
        // \App\Models\Teacher::factory(10)->create();
    }
}
