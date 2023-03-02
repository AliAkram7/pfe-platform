<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Teacher extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'teachers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'institutional_email',
        'personal_email',
        'grade_id',
        'password',
        'tel',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];


    // public function teams()
    // {
    //     return $this->belongsToMany(Team::class);
    // }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {

        // ! check if teacher is department manager #
        // ! check if teacher is speciality manager
        // ! check if teacher is presedents
        // ! check if teacher is doyen

        // *  check if teacher is  department manager #

        $teacherId = $this->id ;

        $isDepartmentManager = teacher_department_manager::select()->where('id_teacher', $teacherId)->count();



        return [
            'role' => 'teacher',
            'department_manager'=>$isDepartmentManager,
        ];
    }




}
