<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherSpecialtyManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_specialty_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id') ;
            $table->unsignedBigInteger('specialty_id')  ;
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('teachers') ;
            $table->foreign('specialty_id')->references('id')->on('specialities') ;
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_specialty_managers');
    }
}
