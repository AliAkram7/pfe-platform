<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presentation extends Model
{
    use HasFactory;



    protected $fillable = [

        'team_id',
        'jury_group_number',
        'presentation_date',
        'testers_group_number',
        'test_project_date',
    ];


}
