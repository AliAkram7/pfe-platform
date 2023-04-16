<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class teacher_department_manager extends Model
{
    use HasFactory;


    protected $table ='teacher_department_managers' ;


    protected $fillable = [
        'id_teacher' , 'id_department'
        ] ;



}
