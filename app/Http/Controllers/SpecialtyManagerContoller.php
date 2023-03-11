<?php

namespace App\Http\Controllers;

use App\Models\Specialitie;
use App\Models\Student;
use App\Models\Teacher_specialty_manager;
use Illuminate\Http\Request;

class SpecialtyManagerContoller extends Controller
{
    public function fetchSpecialtyInfo(Request $request)
    {
        $teacher = $request->user('teacher');


        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;

        $specialty_info = Specialitie::select('fullname', 'abbreviated_name', 'departments.name')
            ->join('departments', 'departments.id', '=', 'specialities.department_id')
            ->where('specialities.id', $specialty_id)->get()->first();

        return response(compact('specialty_info'), 200);

    }

    public function getRanking(Request $request)
    {
        $teacher = $request->user('teacher');

        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;

        $this_year = date('Y');
        $last_year = " " . $this_year - 1 . "";

        $year_scholar = "$last_year-$this_year";

        $studentRanks = Student::
            select('students.code', 'students.name AS student_name', 'ranks.ms1', 'ranks.ms2', 'ranks.mgc', 'observation')
            ->join('student_specialities', 'students.id', '=', 'student_specialities.student_id')
            ->join('specialities', 'student_specialities.speciality_id', '=', 'specialities.id')
            ->join('ranks', 'student_specialities.id', '=', 'ranks.student_specialite_id')
            ->where('year_scholar', $year_scholar)
            ->where('student_specialities.speciality_id', $specialty_id)
            ->groupBy('specialities.id', 'students.id', 'students.name', 'ranks.ms1', 'ranks.ms2', 'ranks.mgc', 'specialities.fullname', 'year_scholar', 'observation', 'students.code')
            ->orderBy('ranks.mgc', 'desc')
            ->get();

        $response = [
            'year_scholar' => $year_scholar,
            'student_rank' => $studentRanks,
        ];

        return response($response);
    }

}
