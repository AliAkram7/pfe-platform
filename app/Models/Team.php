<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_member',
        'id_supervisor'
    ];



    public function students()
    {
        return $this->belongsToMany(Student::class);
    }

}
