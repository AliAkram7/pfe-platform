<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_1',
        'member_2',
        'year_scholar_id',
        'choice_list',
        'supervisor_id'
    ];



    public function students()
    {
        return $this->belongsToMany(Student::class);
    }

}
