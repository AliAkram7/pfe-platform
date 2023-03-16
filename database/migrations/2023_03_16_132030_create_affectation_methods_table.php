<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffectationMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affectation_methods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('specialty_id');
            $table->unsignedInteger('method') ; // ! 1 reference theme -> framer
                                                                                // ! 2 reference framer ->theme 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affectation_methods');
    }
}
