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
            $table->text('team_member');
            $table->unsignedBigInteger('id_supervisor')->nullable();
            $table->text('themes_ids');
            $table->unsignedBigInteger('team_rank');
            $table->unsignedBigInteger('affectation_result');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teams');
    }
}
