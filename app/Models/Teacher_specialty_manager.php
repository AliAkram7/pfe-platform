<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher_specialty_manager extends Model
{
    use HasFactory;


    protected $fillable = [
        'specialty_id', 'teacher_id',
    ] ;




}
