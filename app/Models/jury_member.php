<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jury_member extends Model
{
    use HasFactory;



    protected $fillable =[
        	'teacher_id' , 	'group_number' , 	'specialty_id'
    ] ; 

}
