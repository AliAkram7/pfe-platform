<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddFramerRequest;
use App\Http\Requests\RemoveFramerRequest;
use App\Models\Framer;
use App\Models\Student_speciality;
use App\Models\Teacher;
use App\Models\Teacher_specialty_manager;
use App\Models\Team;
use Illuminate\Http\Request;

class FramerController extends Controller
{
    public function fetchFramerTeacher(Request $request)
    {
        $teacher = $request->user('teacher');

        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        return Framer::select(
            'teachers.code',
            'teachers.name',
            'teachers.institutional_email',
            'teachers.personal_email',
            'grades.fullname as gradeFullname  ',
            'grades.abbreviated_name as gradeAbName',
            'framers.number_team_accepted'
        )
            ->leftJoin('teachers', 'teachers.id', '=', 'framers.teacher_id')
            ->leftJoin('grades', 'teachers.grade_id', '=', 'grades.id')
            ->where('specialty_id', $specialty_managed_id)
            ->get();

    }


    public function addFramer(AddFramerRequest $request)
    {
        $credentials = $request->validated();

        $teacher = $request->user('teacher');

        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        $teacher_id = Teacher::select('*')->where('code', $credentials['code'])->get()->first()->id;

        try {
            Framer::create(
                [
                    'teacher_id' => $teacher_id,
                    'specialty_id' => $specialty_managed_id,
                    'number_team_accepted' => $credentials['numberOfAcceptedTeam']
                ]
            );

        } catch (\Throwable $th) {
            return response('add teacher error', 500);
        }
        return response('successfully added teacher', 201);

    }

    public function removeFarmer(RemoveFramerRequest $request)
    {
        $credentials = $request->validated();

        $teacher = $request->user('teacher');

        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        $teacher_id = Teacher::select('*')->where('code', $credentials['code'])->get()->first()->id;

        try {
            \DB::delete('delete FROM framers  where teacher_id  = ?  and specialty_id = ?   ', [$teacher_id, $specialty_managed_id]);

        } catch (\Throwable $th) {
            return response('error in delete', 500);
        }

        return response('deleted successfully', 200);

    }

    public function getTeacherNotFramer(Request $request)
    {

        $teacher = $request->user('teacher');

        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        return \DB::table('teachers as t1')
            ->join('grades as t3', 't1.grade_id', '=', 't3.id')
            ->select(
                'code AS value',
                \DB::raw("CONCAT('teacher: ',name, ' with code : ', code) AS label"),

            )
            ->whereNotIn('t1.id', function ($query) use ($specialty_managed_id) {
                $query->select('teacher_id')->from('framers')
                    ->where('specialty_id', '=', $specialty_managed_id);
            })
            ->get();


    }



    public function publishListOfFarmers(Request $request)
    {

        $teacher = $request->user('teacher') ;

        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;



        $teams = Team::select('teams.id')
        ->join('student_specialities', 'student_specialities.student_id', '=', 'teams.member_1')
        ->where('speciality_id', $specialty_managed_id)
        ->orderBy('teams.id')
        ->get() ;






        foreach ($teams as $team) {

            $matchingThemes = Framer::where('specialty_id', $specialty_managed_id)
                ->pluck('teacher_id')
                ->toArray();

            $team->choice_list = json_encode($matchingThemes);
            $team->save();
        }

        return response('publish successfully', 201);


    }


}
