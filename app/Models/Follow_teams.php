<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow_teams extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id' ,
        'period_id' ,
    ] ; 


}
