<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student_speciality extends Model
{
    use HasFactory;

    protected $table = 'student_specialities';
    protected $fillable = [
        'student_id',
        'speciality_id',
        'year_scholar'
    ];


}
