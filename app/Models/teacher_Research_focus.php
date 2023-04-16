<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class teacher_Research_focus extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'Research_focus_id'
    ];

}
