<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {

            $table->id();
            $table->string("code");
            $table->string('name');
            $table->string('personal_email')->unique()->nullable();
            $table->string('institutional_email')->unique()->nullable();
            $table->string('tel')->nullable();
            $table->unsignedBigInteger('grade_id');
            $table->string('password');
            $table->timestamps();

            $table->foreign('grade_id')->references('id')->on('grades');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teachers');
    }
}
