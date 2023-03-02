<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsAccountSeedersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students_account_seeders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('code')->unique() ;
            $table->string('name');
            $table->string('default_password');
            $table->boolean('logged');
            $table->timestamp('logged_at');
            $table->boolean('account_status');
            $table->unsignedBigInteger('specialty_id');
            $table->year('year_scholar');
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
        Schema::dropIfExists('students_account_seeders');
    }
}
