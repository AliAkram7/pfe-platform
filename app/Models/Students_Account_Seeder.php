<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Students_Account_Seeder extends Model
{
    use HasFactory;

        protected  $table = 'students_account_seeders' ;

        protected $fillable = [
            'code', 'name', 'default_password','specialty_id'
        ] ; 

}
