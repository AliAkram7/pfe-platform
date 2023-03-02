<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherDepartmentManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_department_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_teacher') ;
            $table->unsignedBigInteger('id_department');
            $table->timestamps();
            $table->foreign('id_teacher')->references('id')->on('teachers') ;
            $table->foreign('id_department')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_department_managers');
    }
}
