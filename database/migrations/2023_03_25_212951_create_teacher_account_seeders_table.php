<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherAccountSeedersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_account_seeders', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('code')->unique() ;
            $table->string('name');
            $table->String('institutional_email')->unique() ;
            $table->unsignedBigInteger('grade') ;
            $table->boolean('logged')->default(false);
            $table->date('logged_at')->default(null);
            $table->boolean('account_status')->default(true);
            $table->timestamps();

            $table->foreign('grade')->references('id')->on('grades') ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_account_seeders');
    }
}
