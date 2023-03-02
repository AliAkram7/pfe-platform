<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'creater_id','team_id', 'room_name', 'discription',
    ] ;


}
