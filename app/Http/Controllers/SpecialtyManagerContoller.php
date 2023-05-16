<?php

namespace App\Http\Controllers;

use App\Http\Requests\getAppointmentsDatesRequest;
use App\Models\Affectation_method;
use App\Models\Follow_teams;
use App\Models\Specialitie;
use App\Models\Student;
use App\Models\Students_Account_Seeder;
use App\Models\Teacher;
use App\Models\Teacher_specialty_manager;
use App\Models\Team_appointment;
use App\Models\Theme;
use App\Models\Team;
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

    public function getRanking(Request $request, $year_id)
    {
        $teacher = $request->user('teacher');

        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;

        $this_year = date('Y');
        // $last_year = " " . $this_year - 1 . "";

        // $year_scholar = "$last_year-$this_year";

        $studentRanks = Students_Account_Seeder::
            select('students_account_seeders.code', 'students_account_seeders.name AS student_name', 'ranks.ms1', 'ranks.ms2', 'ranks.mgc', 'observation')
            ->join('student_specialities', 'students_account_seeders.id', '=', 'student_specialities.student_id')
            ->join('specialities', 'student_specialities.speciality_id', '=', 'specialities.id')
            ->join('ranks', 'student_specialities.id', '=', 'ranks.student_specialite_id')
            ->where('student_specialities.year_scholar_id', $year_id)
            ->where('student_specialities.speciality_id', $specialty_id)
            ->groupBy('specialities.id', 'students_account_seeders.id', 'students_account_seeders.name', 'ranks.ms1', 'ranks.ms2', 'ranks.mgc', 'specialities.fullname', 'observation', 'students_account_seeders.code')
            ->orderBy('ranks.mgc', 'desc')
            ->get();

        $response = [
            'year_scholar' => $this_year,
            'student_rank' => $studentRanks,
        ];

        return response($response);
    }

    //* Teams management

    public function fetchTeams(Request $request, $selectedYearId)
    {

        // ** fetch all teams that one of the member is in specialty of the specialty manager


        $teacher = $request->user();

        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;

        $teams = \DB::table('teams')
            ->leftJoin('students_account_seeders as s', function ($join) {
                $join->on('teams.member_1', '=', 's.id')
                    ->orOn('teams.member_2', '=', 's.id');
            })
            ->leftJoin('student_specialities as ss', 's.id', '=', 'ss.student_id')
            ->leftJoin('year_scholars as ys', 'ys.id', '=', 'ss.year_scholar_id')
            ->leftJoin('specialities as sp', 'ss.speciality_id', '=', 'sp.id')
            ->select(
                'teams.id',
                'teams.year_scholar_id',
                'member_1',
                'member_2',
                'supervisor_id',
                'choice_list',
                'theme_id',
            )
            ->where('teams.year_scholar_id', $selectedYearId)
            ->where('sp.id', '=', $specialty_id)
            ->distinct()
            ->get()

        ;

        // return $teams;

        // return $teams ;
        $response = [];
        // get students snd supervisor of team information
        foreach ($teams as $team) {

            $supervisor_info = Teacher::
                select('name', 'institutional_email', 'abbreviated_name', 'fullname', "teachers.code AS  teacher_code")
                ->leftJoin('grades', 'teachers.grade_id', '=', 'grades.id')
                ->leftJoin('teams', 'teams.supervisor_id', '=', 'teachers.id')
                ->where('supervisor_id', $team->supervisor_id)
                ->where('teams.id', $team->id)
                ->get()->first();

            $member_1_info = Students_Account_Seeder::select('name', 'code')
                ->where('students_account_seeders.id', $team->member_1)->get()->first();

            $member_2_info = Students_Account_Seeder::select('name', 'code')
                ->where('students_account_seeders.id', $team->member_2)->get()->first();


            $array_of_themes_ids = json_decode($team->choice_list);

            $list_theme = [];
            // !! if method of affectation is one
            $method_of_aff = Affectation_method::select('method')->where('specialty_id', $specialty_id)->get()->first()->method;
            if ($method_of_aff == 2) {
                foreach ($array_of_themes_ids as $theme_id) {
                    $list_theme[] = Teacher::select('name as title')->where('id', $theme_id)->first();
                }
            } else {
                foreach ($array_of_themes_ids as $theme_id) {
                    $list_theme[] = Theme::select('title')->where('id', $theme_id)->first();
                }
            }


            // * fetch period of team

            $periods = Follow_teams::select('periods.id AS p_id', 'num_period', 'start_date', 'end_date')
                ->leftJoin('periods', 'periods.id', '=', 'follow_teams.period_id')
                ->where('team_id', $team->id)
                ->get()
            ;
            $theme = Theme::select(
                'id',
                'title',
                'description',
                'research_domain',
                'objectives_of_the_project',
                'key_word',
                'work_plan',
                'created_at AS send_at'
            )->where('id', $team->theme_id)->first();

            $response[] = [
                'team_id' => $team->id,
                'supervisor_info' => $supervisor_info,
                'member_1' => $member_1_info,
                'member_2' => $member_2_info,
                'list_theme' => $list_theme,
                "theme_workOn" => $theme,
                'periods' => $periods,
            ];
        }
        return $response;
    }



}
