<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team_appointment extends Model
{
    use HasFactory;


    protected $fillable = [
        'date' , 
     	'follow_team_id' ,
     	'state_of_progress' ,
     	'Required_work' ,
     	'type_of_session' ,
     	'observation' ,
    ] ;

}
