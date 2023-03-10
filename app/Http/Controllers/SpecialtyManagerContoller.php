<?php

namespace App\Http\Controllers;

use App\Models\Specialitie;
use App\Models\Teacher_specialty_manager;
use Illuminate\Http\Request;

class SpecialtyManagerContoller extends Controller
{
    public function fetchSpecialtyInfo(Request $request)
    {
        $teacher =  $request->user('teacher') ;


        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id ;

        $specialty_info  = Specialitie::select('fullname','abbreviated_name', 'departments.name' )
        ->join('departments', 'departments.id', '=', 'specialities.department_id')
        ->where('specialities.id', $specialty_id)->get()->first() ;

        return response(compact('specialty_info'), 200) ;

    }
}
