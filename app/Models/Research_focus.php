<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Research_focus extends Model
{
    use HasFactory;
    protected $fillable = [

        'id',
        'Axes_and_themes_of_recherche'
    ];
}
