<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ranks', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('student_specialite_id');

            $table->decimal('ms1', 4, 2)->unsigned()->default(0);
            $table->decimal('ms2', 4, 2)->unsigned()->default(0);
            $table->decimal('mgc', 4, 2)->unsigned()->default(0);

            $table->integer('observation')->unsigned()->default(0);

            $table->timestamps();

            $table->foreign('student_specialite_id')->references('id')->on('student_specialities');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ranks');
    }
}
