<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('follow_team_id');
            $table->date('date') ;
            $table->string('state_of_progress') ;
            $table->string('Required work') ;
            $table->string('type_of_session') ;
            $table->String('observation') ;
            $table->timestamps();

            $table->foreign('follow_team_id')->references('id')->on('follow_teams') ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_appointments');
    }
}
