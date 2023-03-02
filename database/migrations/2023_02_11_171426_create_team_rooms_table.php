<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id') ;
            $table->string('room_name');
            $table->text('discription')->nullable();
            $table->unsignedBigInteger('creater_id') ;
            $table->timestamps();
            $table->foreign('team_id')->references('id')->on('teams') ;

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('team_rooms');
    }
}
