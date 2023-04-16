<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'specialty_id',
        'description',
        'title',
        'key_word',
        'research_domain',
        'objectives_of_the_project',
        'work_plan',
        'specialty_manager_validation',
    ];


}
