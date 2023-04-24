<?php

namespace App\Http\Controllers\SESSEION;

use App\Http\Controllers\Controller;
use App\Http\Requests\addSingleStudentinTeamRequest;
use App\Http\Requests\CreateRoomRequest;
use App\Http\Requests\updateListOfThemeRequest;
use App\Models\Affectation_method;
use App\Models\Framer;
use App\Models\Student;
use App\Models\Students_Account_Seeder;
use App\Models\Student_speciality;
use App\Models\Teacher;
use App\Models\Teacher_specialty_manager;
use App\Models\Team;
use App\Models\TeamRoom;
use App\Models\Theme;
use Illuminate\Http\Request;


class TeamsController extends Controller
{


    public function getRooms(Request $request)
    {
        // TODO  still have to join the sender id with the supervisor id
        $student = $request->user("student");

        //* get partner information
        //  ! retrive members ids from Team

        $studentId = $student->id;

        $team_info = Team::select('id', 'member_1', 'member_2', )->where('member_1', $studentId)->orWhere('member_2', $studentId)->get()->first();

        // $fetch_rooms = TeamRoom::
        //     select('team_rooms.id AS  id_room', 'name', 'team_rooms.created_at', 'room_name', 'team_rooms.discription', 'creater_id')
        //     ->leftJoin('teams', 'teams.id' , '=' , 'team_id')
        //     ->leftJoin('students', 'students.id', '=', 'teams.member_1')
        //     ->leftJoin('students', 'students.id', '=', 'teams.member_2')
        //     ->leftJoin('teachers', 'teacher.id', '=', 'teams.supervisor_id')
        //     ->where('team_id', $team_info->id)
        //     ->orderBy('created_at', 'desc')
        //     ->get()->map(function ($room) {
        //         $room->name = $room->creater_id != 4 ? $room->name : 'system' ;
        //         return $room;
        //     });


        $fetch_rooms = TeamRoom::select(
            'team_rooms.id AS id_room',
            \DB::raw("CASE
                WHEN creater_id = 1 THEN IFNULL(s1.name, 'Unknown')
                WHEN creater_id = 2 THEN IFNULL(s2.name, 'Unknown')
                WHEN creater_id = 3 THEN IFNULL(t.name, 'Unknown')
                ELSE 'system'
            END as name"),
            'team_rooms.created_at',
            'room_name',
            'team_rooms.discription',
            'creater_id')
        ->leftJoin('teams', 'teams.id', '=', 'team_id')
        ->leftJoin('students as s1', 's1.id', '=', 'teams.member_1')
        ->leftJoin('students as s2', 's2.id', '=', 'teams.member_2')
        ->leftJoin('teachers as t', 't.id', '=', 'teams.supervisor_id')
        ->where('team_id', $team_info->id)
        ->orderBy('created_at', 'desc')
        ->get();
        return $fetch_rooms;
    }

    public function createRoom(CreateRoomRequest $request)
    {
        // !  verfy id team in supervisor pages

        $sender = $request->user();
        $senderId = $sender->id;

        $team_info = Team::select('id', 'member_1', 'member_2', )->where('member_1', $senderId)->orWhere('member_2', $senderId)->get()->first();


        TeamRoom::create([
            'team_id' => $team_info->id,
            'room_name' => $request['roomName'],
            'discription' => $request['roomDiscription'],
            'creater_id' => $senderId,
        ]);

        return response('room created ', 200);

    }

    public function getListOfTeams(Request $request)
    {
        // !! fetch list of the teams that the teacher lead !!

        $teacher_id = $request->user()->id;

        $Teams = Team::select('member_1', 'member_2', 'id')->where('supervisor_id', $teacher_id)->get();

        $teams_list = array();
        $team_info = array();

        foreach ($Teams as $team) {
            $member_ids = [$team->member_1, $team->member_2];

            foreach ($member_ids as $member_id) {
                $student_info = Student::find($member_id);
                $student_resource_info = $student_info;
                if ($student_resource_info) {
                    $team_info[] = $student_resource_info;
                }
            }

            $teams_list[] = ['team_id' => $team->id, $team_info];

            $team_info = array();
        }


        return count($teams_list) > 0 ? response(compact('teams_list'), 200) : response('', 200);

    }

    // !! get all rooms of team for teacher

    public function getRoomsByTeam(Request $request, $id)
    {
        // $fetch_rooms = TeamRoom::
        //     select('team_rooms.id AS  id_room', 'name', 'team_rooms.created_at', 'room_name', 'team_rooms.discription')
        //     ->join('students', 'students.id', '=', 'team_rooms.creater_id')
        //     ->where('team_id', $id)
        //     ->get();



            $fetch_rooms = TeamRoom::select(
                'team_rooms.id AS id_room',
                \DB::raw("CASE
                    WHEN creater_id = 1 THEN IFNULL(s1.name, 'Unknown')
                    WHEN creater_id = 2 THEN IFNULL(s2.name, 'Unknown')
                    WHEN creater_id = 3 THEN IFNULL(t.name, 'Unknown')
                    ELSE 'system'
                END as name"),
                'team_rooms.created_at',
                'room_name',
                'team_rooms.discription',
                'creater_id')
            ->leftJoin('teams', 'teams.id', '=', 'team_id')
            ->leftJoin('students as s1', 's1.id', '=', 'teams.member_1')
            ->leftJoin('students as s2', 's2.id', '=', 'teams.member_2')
            ->leftJoin('teachers as t', 't.id', '=', 'teams.supervisor_id')
            ->where('team_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();


        return $fetch_rooms;

    }

    public function updateListOfThemeChooses(updateListOfThemeRequest $request)
    {

        $credentials = $request->validated();
        $student = $request->user();


        $team = Team::where('member_1', $student->id)->orWhere('member_2', $student->id)->first();


        // try {
        \DB::update('update teams set choice_list = ?  where id = ? ', [json_encode(($credentials['theme_list'])), $team->id]);
        return response('updated successfully', 200);
        // } catch (\Throwable $th) {

        //     return response('error happen', 500);

        // }




    }

    public function fetchSingleStudents(Request $request)
    {
        $teacher = $request->user('teacher');


        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;

        return $students = Students_Account_Seeder::select(
            'code AS value',
            \DB::raw("CONCAT('student: ',name, ' with code : ', code) AS label"),
        )

            ->leftJoin('student_specialities', 'students_account_seeders.id', '=', 'student_specialities.student_id')
            ->where('student_specialities.speciality_id', '=', $specialty_id)
            ->whereNotIn('students_account_seeders.id', function ($query) {
                $query->select('students_account_seeders.id')
                    ->from('students_account_seeders')
                    ->leftJoin('teams', function ($join) {
                            $join->on('students_account_seeders.id', '=', 'teams.member_1')
                                ->orWhere('students_account_seeders.id', '=', 'teams.member_2');
                        })
                    ->whereNotNull('teams.id');
            })
            ->get();

    }

    public function addSingleStudentInTeam(addSingleStudentinTeamRequest $request)
    {

        $credentials = $request->validated();
        $student_id = Students_Account_Seeder::select('id')->where('code', $credentials['code'])->get()->first()->id;
        if (Team::create(['member_1' => $student_id])) {
            return response('student add successfully', 201);
        } else
            return response('error', 403);
    }


    public function resultOfAffectation(Request $request)
    {
        // !! teacher or students
        $student = $request->user('student');


        $specialty_id = Student_speciality::select()->where('student_id', $student->id)->get()->first()->speciality_id;



        // $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;

        $teams = \DB::table('teams')
            ->join('students as s', function ($join) {
                $join->on('teams.member_1', '=', 's.id')
                    ->orWhere('teams.member_2', '=', 's.id');
            })
            ->join('student_specialities as ss', 's.id', '=', 'ss.student_id')
            ->join('specialities as sp', 'ss.speciality_id', '=', 'sp.id')
            ->where('sp.id', '=', $specialty_id)
            ->select(
                'teams.id',
                'member_1',
                'member_2',
                'supervisor_id',
                'choice_list',
          
                'theme_id',
                // '*'
            )
            ->get();
        $method_of_aff = Affectation_method::select('method')->where('specialty_id', $specialty_id)->get()->first()->method;

        $not_sorted_list_indexed = [];
        if ($method_of_aff == 2) {
            $not_sorted_list = Framer::select('name as title')
                ->leftJoin('teachers', 'teachers.id', '=', 'framers.teacher_id')
                ->where('specialty_id', $specialty_id)->get();
        } else {
            $not_sorted_list = Theme::select('title')->where('specialty_id', $specialty_id)->where('specialty_manager_validation', 1)->get();
        }

        $i = 1;
        foreach ($not_sorted_list as $item) {
            $not_sorted_list_indexed[$i] = $item;
            $not_sorted_list_indexed[$i]['index'] = $i; // Add an index to the current element
            $i++;
        }
        // return $teams ;
        $response = [];
        // get students snd supervisor of team information

        foreach ($teams as $team) {

            $supervisor_info = Teacher::
                select('name')
                ->leftJoin('teams', 'teams.supervisor_id', '=', 'teachers.id')
                ->where('supervisor_id', $team->supervisor_id)
                ->where('teams.id', $team->id)
                ->get()->first();

            $member_1_info = Students_Account_Seeder::select('name')
                ->where('students_account_seeders.id', $team->member_1)->get()->first();

            $member_2_info = Students_Account_Seeder::select('name')
                ->where('students_account_seeders.id', $team->member_2)->get()->first();


            $array_of_themes_ids = json_decode($team->choice_list);

            $list_theme_not_indexed = [];
            // !! if method of affectation is one
            if ($method_of_aff == 2) {
                foreach ($array_of_themes_ids as $theme_id) {
                    $list_theme_not_indexed[] = [Teacher::select('name as title')->where('id', $theme_id)->first()];
                }
            } else {
                foreach ($array_of_themes_ids as $theme_id) {
                    $list_theme_not_indexed[] = Theme::select('title')->where('id', $theme_id)->first();

                }
            }

            $theme = Theme::select('title')->where('id', $team->theme_id)->first();

            $list_theme = [];

            $i = 1;
            foreach ($list_theme_not_indexed as $item) {
                // $not_sorted_list_indexed  ;
                $list_theme[$i] = $item;

            }


            $response[] = [
                'supervisor_info' => $supervisor_info,
                'member_1' => $member_1_info,
                'member_2' => $member_2_info,
                'list_theme' => $list_theme,
                "theme_workOn" => $theme,
            ];
            $i = 0;
        }







        return response(compact('response', 'not_sorted_list_indexed'), 200);

    }








}
