<?php

namespace App\Http\Controllers;

use App\Http\Requests\createPeriodRequest;
use App\Http\Requests\fetchAppointmentDataRequest;
use App\Http\Requests\getAppointmentsDatesRequest;
use App\Models\Follow_teams;
use App\Models\Period;
use App\Models\Teacher_specialty_manager;
use App\Models\TeamRoom;
use App\Models\Team_appointment;
use Illuminate\Http\Request;

class PeriodController extends Controller
{

    public function createPeriod(createPeriodRequest $request)
    {
        $credentials = $request->validated();

        $teacher = $request->user('teacher');

        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;




        $teams = \DB::table('teams')
            ->join('students_account_seeders as s', function ($join) {
                $join->on('teams.member_1', '=', 's.id')
                    ->orWhere('teams.member_2', '=', 's.id');
            })
            ->join('student_specialities as ss', 's.id', '=', 'ss.student_id')
            // ->join('student_specialities as ss', 's.id', '=', 'ss.student_id')
            ->join('specialities as sp', 'ss.speciality_id', '=', 'sp.id')
            ->join('year_scholars as ys', 'ys.id', '=', 'ss.year_scholar_id')
            ->where('teams.year_scholar_id', $credentials['selectedYearId'])
            ->where('sp.id', '=', $specialty_id)
            ->select('teams.id')
            ->distinct()
            ->get()
        ;


        // try {
            $period = Period::create([
                "num_period" => $credentials['nPeriod'],
                "start_date" => $credentials['start_date'],
                "end_date" => $credentials['end_date'],
            ]);
        // } catch (\Throwable $th) {
        //     return response("error in create period", 403);
        // }
        foreach ($teams as $team) {
            if (
                Follow_teams::create([
                    'team_id' => $team->id,
                    "period_id" => $period->id,
                ])
            ) {
                TeamRoom::create([
                    'team_id' => $team->id,
                    'creater_id' => 4,
                    'room_name' => "meeting period started " . $period->num_period,
                    'discription' => "meeting period number " . $period->num_period .
                    " start date : " . $period->start_date .
                    " end date : " . $period->end_date,
                ]);
            }
        }

        response('period created', 201);

        // * every team in this specialty fellow this period

    }


    public function fetchPeriods(Request $request)
    {
        $teacher = $request->validated();
    }

    public function getAppointmentsDates(getAppointmentsDatesRequest $request)
    {
        $credentials = $request->validated();
        $teacher = $request->user('teacher');

        $follow_team = Follow_teams::select('id')
            ->where('team_id', $credentials['team_id'])
            ->where('period_id', $credentials['PID'])
            ->get()
            ->first();


        return Team_appointment::select('id', 'follow_team_id', 'date')
            ->where('follow_team_id', $follow_team->id)
            ->get();

    }


    public function fetchAppointmentData(fetchAppointmentDataRequest $request)
    {
        $credentials = $request->validated();

        return Team_appointment::select()->where('id', $credentials['appointment_id'])->get()->first();

    }




}
