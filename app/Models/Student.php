<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;




class Student extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'students';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'tel',
        'code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];



    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

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




        //* get partner information
        //  ! retrieve members ids from Team

        $studentId = $this->id;
        try {

            $isInTeam = false;
            if (
                $checkIfStudentISInTeam = Team::select('*')
                    ->where('member_1', $studentId)
                    ->orWhere('member_2', $studentId)->get()
            ) {
                if (count($checkIfStudentISInTeam) == 1) {
                    $isInTeam = true;
                }
            }
        } catch (\ErrorException $th) {
            $isInTeam = false;
        }


        $logged = Students_Account_Seeder::select('logged')->where('code', $this->code)->get()->first()['logged'];

        $specialty_id = Student_speciality::select('*')->where('student_id', $this->id)->get()->first()->speciality_id;
        $method_of_aff = Affectation_method::select('method')->where('specialty_id', $specialty_id)->get()->first()->method;


        return [
            'role' => 'student',
            'first_login' => !$logged,
            'isInTeam' => $isInTeam,
            'aff_method' =>$method_of_aff ,
        ];
    }




}
