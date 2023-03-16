<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->required();
            $table->text('description')->nullable();
            $table->text('research_domain')->nullable() ;
            $table->text('key_word')->nullable() ;
            $table->text('objectives_of_the_project')->nullable() ;
            $table->text('work_plan')->nullable() ;
            $table->unsignedBigInteger('specialty_id');
            $table->unsignedBigInteger('teacher_id');
            $table->boolean('specialty_manager_validation')->default(false);
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
        Schema::dropIfExists('themes');
    }
}
