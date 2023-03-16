<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Framer extends Model
{
    use HasFactory;


    protected $fillable =[
    	'teacher_id' 	, 'specialty_id' 	, 'number_team_accepted'
    ] ;


}
