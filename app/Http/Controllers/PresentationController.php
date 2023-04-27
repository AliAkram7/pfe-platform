<?php

namespace App\Http\Controllers;

use App\Http\Requests\affectJuryToTeamsRnRequest;
use App\Http\Requests\createPresentationRequest;
use App\Models\Affectation_method;
use App\Models\jury_member;
use App\Models\Presentation;
use App\Models\Specialitie;
use App\Models\Students_Account_Seeder;
use App\Models\Teacher;
use App\Models\Teacher_specialty_manager;
use App\Models\Theme;
use Illuminate\Http\Request;

class PresentationController extends Controller
{
    public function createPresentation(createPresentationRequest $request)
    {
        $teacher = $request->user('teacher');

        $credentials = $request->validated();
        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        // *  create jury group member


        $dateApp = new \DateTime($credentials['dateApp']);
        $formattedDateApp = $dateApp->format('Y-m-d H:i:s');



        $dateTest = new \DateTime($credentials['dateTest']);
        $formattedDateTest = $dateTest->format('Y-m-d H:i:s');


        $group_number = jury_member::where('specialty_id', $specialty_managed_id)->max('group_number');


        $President = Teacher::select('id')->where('code', $credentials['president'])->get()->first()->id;
        $ex1 = Teacher::select('id')->where('code', $credentials['ex1'])->get()->first()->id;
        $ex2 = Teacher::select('id')->where('code', $credentials['ex2'])->get()->first()->id;
        $ex3 = Teacher::select('id')->where('code', $credentials['ex3'])->get()->first()->id;



        jury_member::create([
            'teacher_id' => $President,
            'group_number' => $group_number + 1,
            'specialty_id' => $specialty_managed_id,
            'isPresident' => true,
        ]);
        jury_member::create([
            'teacher_id' => $ex1,
            'group_number' => $group_number + 1,
            'specialty_id' => $specialty_managed_id,
            'isPresident' => false,
        ]);
        jury_member::create([
            'teacher_id' => $ex2,
            'group_number' => $group_number + 1,
            'specialty_id' => $specialty_managed_id,
            'isPresident' => false,
        ]);
        jury_member::create([
            'teacher_id' => $ex3,
            'group_number' => $group_number + 1,
            'specialty_id' => $specialty_managed_id,
            'isPresident' => false,
        ]);


        // *  create jury group member tester


        $tester_1 = Teacher::select('id')->where('code', $credentials['tester_1'])->get()->first()->id;
        $tester_2 = Teacher::select('id')->where('code', $credentials['tester_2'])->get()->first()->id;

        jury_member::create([
            'teacher_id' => $tester_1,
            'group_number' => $group_number + 2,
            'specialty_id' => $specialty_managed_id,
            'isPresident' => false,
        ]);
        jury_member::create([
            'teacher_id' => $tester_2,
            'group_number' => $group_number + 2,
            'specialty_id' => $specialty_managed_id,
            'isPresident' => false,
        ]);

        Presentation::where('team_id', $credentials['team_id'])->delete();


        Presentation::create([
            'team_id' => $credentials['team_id'],
            'jury_group_number' => $group_number + 1,
            'presentation_date' => $dateApp,
            'testers_group_number' => $group_number + 2,
            'test_project_date' => $dateTest,
        ]);
    }


    public function fetchPresentationDates(Request $request)
    {

        $teacher = $request->user('teacher');

        // $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;


        $specialties = Specialitie::select('id', 'fullname', 'abbreviated_name', 'department_id')->get();



        $allSpecialties = [];
        $specialtyAffectation = [];

        foreach ($specialties as $specialty) {

            $min_date = strtotime('9999-12-31');



            $response = [];

            if (
                $teams = \DB::table('teams')
                    ->join('students_account_seeders as s', function ($join) {
                        $join->on('teams.member_1', '=', 's.id')
                            ->orWhere('teams.member_2', '=', 's.id');
                    })
                    ->join('student_specialities as ss', 's.id', '=', 'ss.student_id')
                    ->join('specialities as sp', 'ss.speciality_id', '=', 'sp.id')
                    ->where('sp.id', '=', $specialty->id)
                    ->select(
                        'teams.id',
                        'member_1',
                        'member_2',
                        'supervisor_id',
                        'theme_id',
                    )->get()
            ) {

                foreach ($teams as $team) {
                    // !! get team presentation information

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
                    if ($team->theme_id != null) {
                        $theme = Theme::select('title')->where('id', $team->theme_id)->first()->title;
                    } else {
                        $theme = null;
                    }

                    if (
                        $presentation = Presentation::
                            leftJoin('jury_members', 'presentations.jury_group_number', '=', 'jury_members.group_number')
                            ->select(
                                'team_id',
                                'jury_group_number',
                                'presentation_date',
                                'testers_group_number',
                                'test_project_date',
                                'teacher_id'
                            )->where('team_id', $team->id)
                            ->get()->first()
                    ) {


                        if ($presentation->presentation_date < $min_date) {
                            $min_date = $presentation->presentation_date;
                        }


                        $jury_group = Presentation::
                            leftJoin('jury_members', 'presentations.jury_group_number', '=', 'jury_members.group_number')
                            ->where('specialty_id', $specialty->id)
                            ->select(
                                'teacher_id'
                            )->where('team_id', $team->id)->get();



                        $teacher_jury = [];

                        foreach ($jury_group as $jury) {
                            $teacher_jury[] = Teacher::select('name')->where('id', $jury['teacher_id'])->get()->first();
                        }

                        $testers_group = Presentation::
                            leftJoin('jury_members', 'presentations.testers_group_number', '=', 'jury_members.group_number')
                            ->select(
                                'teacher_id'
                            )->where('team_id', $team->id)->get();

                        $teacher_tester = [];
                        foreach ($testers_group as $tester) {
                            $teacher_tester[] = Teacher::select('name')->where('id', $tester['teacher_id'])->get()->first();
                        }

                        $response[] = [
                            'supervisor_info' => $supervisor_info,
                            'teacher_jury' => $teacher_jury,
                            'member_1' => $member_1_info,
                            'member_2' => $member_2_info,
                            'theme' => $theme,
                            'date_presentation' => $presentation['presentation_date'],
                            'testers_group' => $teacher_tester,
                            'test_project_date' => $presentation['test_project_date'],
                        ];

                        $teacher_jury = [];


                    }
                }

            }

            if (count($response) > 0) {
                $method_of_aff = Affectation_method::select('method')->where('specialty_id', $specialty->id)->get()->first()->method;
                $specialtyAffectation[] = [
                    'start_appointments' => $min_date,
                    'specialty_name' => $specialty->fullname,
                    'method_of_aff' => $method_of_aff,
                    'abbreviated_name' => $specialty->abbreviated_name,
                    'affectation' => $response
                ];

            }
        }

        return $specialtyAffectation;




    }

    public function affectJuryToTeamsRn(affectJuryToTeamsRnRequest $request)
    {

        $credentials = $request->validated();

        $teacher = $request->user();

        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;



        if (
            $teams = \DB::table('teams')
                ->join('students_account_seeders as s', function ($join) {
                    $join->on('teams.member_1', '=', 's.id')
                        ->orWhere('teams.member_2', '=', 's.id');
                })
                ->join('student_specialities as ss', 's.id', '=', 'ss.student_id')
                ->join('specialities as sp', 'ss.speciality_id', '=', 'sp.id')
                ->where('sp.id', '=', $specialty_managed_id)
                ->select(
                    'teams.id',
                    'member_1',
                    'member_2',
                    'supervisor_id',
                    'theme_id',
                )
                ->get()
        ) {


            $allJuryGroups = jury_member::select('group_number')
                ->where('specialty_id', $specialty_managed_id)
                ->groupBy('group_number')
                ->get();

            $juryGroup_numbers_oc = $allJuryGroups->map(function ($juryGroup) {
                return [
                    'group_number' => $juryGroup->group_number,
                    'oc' => 0,
                ];
            })->toArray();

            foreach ($teams as $team) {

                $supervisorId = $team->supervisor_id;

                $juryGroupsCanAffected = jury_member::select('group_number')
                    ->where('specialty_id', $specialty_managed_id)
                    ->whereNotIn('group_number', function ($query) use ($supervisorId, $specialty_managed_id) {
                        $query->select('group_number')
                            ->from('jury_members')
                            ->where('teacher_id', '=', $supervisorId)
                            ->where('specialty_id', $specialty_managed_id);
                        // ->get();
                    })
                    ->groupBy('group_number')
                    ->get()
                    ->toArray();


                $minOc = null;
                $minGroupNumber = null;

                foreach ($juryGroup_numbers_oc as $juryGroup) {
                    if (in_array($juryGroup['group_number'], array_column($juryGroupsCanAffected, 'group_number'))) {
                        if ($minOc === null || $juryGroup['oc'] < $minOc) {
                            $minOc = $juryGroup['oc'];
                            $minGroupNumber = $juryGroup['group_number'];
                        }
                    }
                }

                $dateTest = new \DateTime($credentials['date_presentation']);
                $formattedDatePresentation = $dateTest->format('Y-m-d H:i:s');

                Presentation::where('team_id', $team->id)->delete();

                if (
                    Presentation::create([
                        'presentation_date' => $formattedDatePresentation,
                        'team_id' => $team->id,
                        'jury_group_number' => $minGroupNumber,
                    ])
                ) {
                    $juryGroup_numbers_oc = collect($juryGroup_numbers_oc)->map(function ($juryGroup) use ($minGroupNumber) {
                        if ($juryGroup['group_number'] == $minGroupNumber) {
                            $juryGroup['oc'] = $juryGroup['oc'] + 1;
                        }
                        return $juryGroup;
                    })->toArray();
                }




            }

        }


        return response('', 201);

    }




}
