<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher_account_seeders extends Model
{
        use  HasFactory ;

        protected $table='teacher_account_seeders' ;
    protected $fillable = [
        'id', 'code', 'name', 'institutional_email' ,'grade'
    ] ;

}
