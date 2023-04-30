<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentSpecialitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_specialities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('speciality_id');
            $table->unsignedBigInteger('year_scholar_id');

            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('speciality_id')->references('id')->on('specialities');
            $table->foreign('year_scholar_id')->references('id')->on('year_scholars');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_specialities');
    }
}
