<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddFramerRequest;
use App\Http\Requests\RemoveFramerRequest;
use App\Models\Framer;
use App\Models\Students_Account_Seeder;
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

        $teacher = $request->user('teacher');

        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        $new_inscription = \DB::table('year_scholars')
            ->select('id AS year_id')
            ->orderByDesc('end_date')
            ->limit(1)
            ->get()->first();


        $teams = Team::select('teams.id')
            ->where('year_scholar_id', $new_inscription->year_id)
            ->join('student_specialities', 'student_specialities.student_id', '=', 'teams.member_1')
            ->where('speciality_id', $specialty_managed_id)
            ->orderBy('teams.id')
            ->get();






        foreach ($teams as $team) {

            $matchingThemes = Framer::where('specialty_id', $specialty_managed_id)
                ->orderBy('created_at')
                ->pluck('teacher_id')
                ->toArray();

            $team->choice_list = json_encode($matchingThemes);
            $team->save();
        }

        return response('publish successfully', 201);


    }


    public function affectFramerToStudents(Request $request)
    {

        // !! dependencies
        // !! specialty_id

        $teacher = $request->user('teacher');
        $specialty_id = Teacher_specialty_manager::select()->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        // !! students array of ids sorted using rank

        $sorted_list_of_students = Students_Account_Seeder::select('students_account_seeders.id', 'mgc')
            ->join('student_specialities', 'student_specialities.student_id', '=', 'students_account_seeders.id')
            ->where('student_specialities.speciality_id', $specialty_id)
            ->join('ranks', 'ranks.student_specialite_id', 'student_specialities.id')
            ->orderBy('mgc', 'desc')
            ->get()->toArray();

        // return $sorted_list_of_students ;

        $sorted_list_of_students_array = array_map(function ($obj) {
            return $obj["id"];
        }, $sorted_list_of_students);


        // !! framers array of available framer
        $list_framer = Framer::select('teacher_id', 'number_team_accepted')->where('specialty_id', $specialty_id)->get()->toArray();



        $list_framer_array = array_map(function ($obj) {
            return [
                "teacher_id" => $obj['teacher_id'],
                'n' => (int) $obj['number_team_accepted']
            ];
        }, $list_framer);



        $student_black_list = [];
        $framer_black_list = [];


        foreach ($sorted_list_of_students_array as $student) {

            // !! get List choice of student


            // return $student /;

            $student_inscription = \DB::table('year_scholars AS ys')
                ->join('student_specialities AS ss', 'ys.id', '=', 'ss.year_scholar_id')
                ->select('ys.id AS year_id', 'ss.id', 'ss.speciality_id', 'ys.end_date')
                ->where('ss.student_id', $student)
                ->orderBy('ys.end_date', 'DESC')
                ->limit(1)
                ->get()
                ->first();

            $team_info = Team::select('id', 'member_1', 'member_2', 'choice_list')
                ->where('year_scholar_id', $student_inscription->year_id)
                ->where(function ($query) use ($student) {
                    $query->where('member_1', $student)
                        ->orWhere('member_2', $student);
                });

            if (in_array($student, $student_black_list)) {
                echo "student $student  in team  $team_info->id  blacklisted\n";
                continue;
            }

            $choice_list = [];
            if ($team_info != null) {
                $choice_list = json_decode($team_info->choice_list);
            }



            if (count($choice_list) > 0) {


                foreach ($choice_list as $framer) {

                    if (in_array($framer, $framer_black_list)) {
                        echo "framer $framer blacklisted \n ";
                        continue;
                    }


                    $found = false;
                    foreach ($list_framer_array as &$item) {
                        if ($item['teacher_id'] == $framer) {
                            echo $item['teacher_id'] . "found\n";
                            $found = true;
                            break;
                        }
                    }


                    if ($found) {

                        if ($framer != null) {

                            if (\DB::update('update teams set  supervisor_id = ?   where id = ?', [$framer, $team_info->id])) {

                                // !! black listed the framer and member_2 and member_1

                                echo "$team_info->id inserted $framer\n";

                                if ($team_info->member_1 != null) {
                                    $student_black_list[] = $team_info->member_1;
                                }
                                if ($team_info->member_2 != null) {
                                    $student_black_list[] = $team_info->member_2;
                                }

                                foreach ($list_framer_array as &$item) {
                                    if ($item['teacher_id'] == $framer) {
                                        $item['n'] = $item['n'] - 1;
                                        if ($item['n'] == 0) {
                                            $framer_black_list[] = $framer;
                                            echo "$framer blacklisted \n";

                                        }

                                        break;
                                    }
                                }

                                break;
                            }
                        }

                    }


                }
            }



        }


    }




}
