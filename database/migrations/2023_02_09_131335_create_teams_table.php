<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('member_1');

            $table->unsignedBigInteger('member_2')->nullable();

            $table->unsignedBigInteger('supervisor_id')->nullable();// * affectation result for the license method

            $table->text('choice_list')->nullable(); // * list of theme or list of teachers

            // $table->unsignedBigInteger('team_rank');

            $table->unsignedBigInteger('theme_id')->nullable();// * theme affectation result

            $table->timestamps();

            $table->foreign('member_1')->references('id')->on('students_account_seeders') ;

            $table->foreign('member_2')->references('id')->on('students_account_seeders') ;

            $table->foreign('supervisor_id')->references('id')->on('teachers') ;

            $table->foreign('theme_id')->references('id')->on('themes') ;






        });



    }

    /**
     * Reverse the migrations.
     *
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teams');
    }
}
