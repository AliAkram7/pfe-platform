<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFramersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('framers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id') ;
            $table->unsignedBigInteger('specialty_id') ;
            $table->decimal('number_team_accepted');
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
        Schema::dropIfExists('framers');
    }
}
