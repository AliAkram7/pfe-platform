<?php

namespace App\Http\Controllers\SESSEION;

use App\Http\Requests\createAppointmentRequest;
use App\Models\Follow_teams;
use App\Models\Team;
use App\Models\TeamRoom;
use App\Models\Team_appointment;
use App\Models\Theme;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Specialitie;
use DateTime;
use Illuminate\Http\Request;
use App\Models\Student_speciality;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Students_Account_Seeder;
use App\Models\teacher_account_seeders;
use App\Http\Requests\sendLicenseThemeRequest;
use App\Http\Requests\teacherUpdateInfoRequest;


class sessionTeacherController extends Controller
{
    public function getTeacherInfo(Request $request)
    {
        $role = "teacher";
        $user = $request->user('teacher');

        $user = Teacher::
            select(
                'teachers.id',
                'teachers.name',
                'teachers.personal_email',
                'teachers.institutional_email',
                'teachers.tel',
                'teachers.code',
                'g.abbreviated_name',
                \DB::raw("CONCAT('[', GROUP_CONCAT(CASE WHEN research_foci.id IS NULL OR research_foci.Axes_and_themes_of_recherche IS NULL THEN '' ELSE JSON_OBJECT('value', COALESCE(research_foci.id, ''), 'label', COALESCE(research_foci.Axes_and_themes_of_recherche, '')) END), ']') AS Axes_and_themes_of_recherche")
            )
            ->leftJoin('grades as g', 'g.id', '=', 'teachers.grade_id')
            ->leftJoin('teacher__research_foci', 'teacher__research_foci.teacher_id', '=', 'teachers.id')
            ->leftJoin('research_foci', 'research_foci.id', '=', 'teacher__research_foci.Research_focus_id')
            ->where('teachers.id', $user->id)
            ->groupBy(
                'teachers.id',
                'teachers.name',
                'teachers.personal_email',
                'teachers.institutional_email',
                'teachers.tel',
                'teachers.code',
                'g.abbreviated_name',
            )

            ->get()
            ->first();



        return response(compact('user', 'role'));

    }

    public function teacherUpdateInfo(teacherUpdateInfoRequest $request)
    {



        // $teacher_account  = Teacher_account_seeders::find($user ->id) ;
        //


        $credentials = $request->validated();

        $teacher = Auth::guard('teacher')->user();

        $teacher_account = Teacher_account_seeders::find($teacher->id);

        if ($teacher_account['logged'] == false) {

            Teacher_account_seeders::where('id', $teacher->id)->update([
                'logged' => true,
                'logged_at' => date('Y-m-d H:i:s')
            ]);

            if (!empty($credentials['newPassword'])) {
                $teacher->update(['password' => bcrypt($credentials['newPassword'])]);
            }
            if ($teacher->update($request->all())) {
                return response('updated', 201);
            } else {
                return response('', 204);
            }


        } else {

            if (!$teacher || !Hash::check($credentials['prPassword'], $teacher->password)) {
                return response('bad cred', 403);
            }
            if (!empty($credentials['newPassword'])) {
                $teacher->update(['password' => bcrypt($credentials['newPassword'])]);
            }
            if ($teacher->update($request->all())) {
                return response('updated', 201);
            } else {
                return response('', 204);
            }
        }
    }
    public function refreshToken(Request $request)
    {
        $account_status = Teacher_account_seeders::select('account_status')->where('code', $request->user('teacher')->code)->get()->first();

        if ($account_status->account_status == 0) {
            return response(["message" => 'Unauthorized'], 401);
        }

        $logged = Teacher_account_seeders::select('logged')->where('code', $request->user()->code)->get()->first()['logged'];

        $teacher = $request->user();

        $token = auth()->claims($teacher->getJWTCustomClaims())->refresh(false, true);

        return response(compact('token'), 200);

    }

    // !! teacher fetch license student

    public function fetchLicenseTeams(Request $request)
    {

        $teacher_id = $request->user()->id;

        $new_inscription = \DB::table('year_scholars')
            ->select('id AS year_id')
            ->orderByDesc('end_date')
            ->limit(1)
            ->get()->first();


        $Teams = Team::select('member_1', 'member_2', 'teams.id', 'speciality_id', 'method', 'fullname AS specialty_name', 'departments.name AS dep_name', 'specialty_id')
            ->leftJoin('student_specialities', 'student_specialities.student_id', '=', 'member_1')
            ->where('teams.year_scholar_id', $new_inscription->year_id)
            ->leftJoin('affectation_methods', 'affectation_methods.specialty_id', '=', 'speciality_id')
            ->leftJoin('specialities', 'specialities.id', '=', 'speciality_id')
            ->leftJoin('departments', 'departments.id', '=', 'specialities.department_id')
            ->where('supervisor_id', $teacher_id)->get();

        // return $Teams  ;

        $teams_list = array();

        $team_info = array();

        foreach ($Teams as $team) {
            if ($team->method == 2) {

                $member_ids = [$team->member_1, $team->member_2];

                foreach ($member_ids as $member_id) {

                    $student_info = Students_Account_Seeder::select('name')->where('id', $member_id)->get()->first();

                    if ($team->method) {
                        $student_resource_info = $student_info;
                        if ($student_resource_info) {
                            $team_info[] = $student_resource_info;
                        }
                    }
                }

                $teams_list[] = [
                    'team_id' => $team->id,
                    "team_info" => $team_info,
                    'specialty_name' => $team->specialty_name,
                    'specialty_id' => $team->specialty_id,
                    'dep_name' => $team->dep_name
                ];

                $team_info = array();
            }
        }

        return count($teams_list) > 0 ? response(compact('teams_list'), 200) : response('', 200);

    }


    public function sendLicenseTheme(sendLicenseThemeRequest $request)
    {
        $credentials = $request->validated();

        $teacher = $request->user('teacher');
        $theme = Theme::create([
            'title' => $credentials['title'],
            'description' => $credentials['description'],
            'teacher_id' => $teacher->id,
            'specialty_id' => $credentials['specialty_id'],
        ]);

        Team::where('id', $credentials['team_id'])->update(['theme_id' => $theme->id]);

        $theme_info = Theme::find($theme);
        $framer_info = Teacher::find($teacher->id);

        TeamRoom::create([
            'team_id' => $credentials['team_id'],
            'creater_id' => 4,
            'room_name' => "theme work on",
            'discription' => $theme_info[0]['title'] . " by " . $framer_info->name .
            ", This theme delves into : " .
            $theme_info[0]['description'] . "."
        ]);
    }




    public function createAppointment(createAppointmentRequest $request)
    {

        $credentials = $request->validated();
        $teacher = $request->user('teacher');

        // * find range depend the date used by the teacher


        $date = new DateTime($credentials['dateApp']);
        $formattedDate = $date->format('Y-m-d H:i:s');




        $follow_teams = Follow_teams::select('follow_teams.id')
            ->leftJoin('periods', 'periods.id', '=', 'period_id')
            ->where('start_date', '<=', $formattedDate)
            ->where('end_date', '>=', $formattedDate)
            ->where('team_id', $credentials['team_id'])->get();

        $isInserted = false;

        foreach ($follow_teams as $follow_team) {

            if (
                Team_appointment::create([
                    'follow_team_id' => $follow_team->id,
                    'date' => $formattedDate,
                    'state_of_progress' => $credentials['stateOfProgress'],
                    'Required_work' => $credentials['requiredWork'],
                    'type_of_session' => $credentials['typeOfSession'],
                    'observation' => $credentials['observation'],
                ])
            ) {
                $isInserted = true;
            }

        }


        if ($isInserted) {


            $formattedDate2 = $date->format("l, F j, Y \a\\t g:i A");

            TeamRoom::create([
                'team_id' => $credentials['team_id'],
                'creater_id' => 3,
                'room_name' => "Hello students,",
                'discription' => "I hope this message finds you well. I just wanted to remind you that we have an important appointment scheduled for "
                . $formattedDate."\n"
                ."observation : " .$credentials['observation']

                ,
            ]);
        }


    }

}
