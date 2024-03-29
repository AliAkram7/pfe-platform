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
        'id',
        'name',
        'code',
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


    // public function grades()
    // {
    //     return $this->belongsToMany(grade::class);
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
        // ! check if teacher is specialty manager
        // ! check if teacher is president
        // ! check if teacher is doyen

        // *  check if teacher is  department manager #

        $teacherId = $this->id;

        $isDepartmentManager = teacher_department_manager::select()->where('id_teacher', $teacherId)->count();
        $isSpecialtyManager = Teacher_specialty_manager::select()->where('teacher_id', $teacherId)->count();
        $pfe_method = 0;
        if ($isSpecialtyManager) {
            $specialty_managed = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacherId)->get()->first();

            if ($affectation_method = Affectation_method::select('method')->where('specialty_id', $specialty_managed->specialty_id)->first()) {
                $pfe_method = $affectation_method->method;
            }
        }

        $logged = Teacher_account_seeders::select('logged')->where('code', $this->code)->get()->first()['logged'];


        $year_scholar_id = \DB::table('year_scholars')
            ->select('id AS year_id')
            ->orderByDesc('end_date')
            ->limit(1)
            ->get()->first()->year_id;



        return [
            'role' => 'teacher',
            'first_login' => !$logged,
            'department_manager' => $isDepartmentManager,
            'specialty_manager' => $isSpecialtyManager,
            'pfe_method' => $pfe_method,
            'year_scholar_id' => $year_scholar_id
        ];
    }




}
