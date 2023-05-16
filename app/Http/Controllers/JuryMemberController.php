<?php

namespace App\Http\Controllers;

use App\Http\Requests\sendListOfLicenseJuryRequest;
use App\Models\jury_member;
use App\Models\Teacher;
use App\Models\Teacher_specialty_manager;
use Illuminate\Http\Request;
use Nette\Utils\Strings;

class JuryMemberController extends Controller
{
    public function fetchTeachers(Request $request, $supervisor_code)
    {
        $teacher = $request->user('teacher');
        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        return Teacher::
            // leftJoin('jury_members', 'jury_members.teacher_id', '=', 'teachers.id')
            leftJoin('teacher__research_foci', 'teacher__research_foci.teacher_id', '=', 'teachers.id')
            ->leftJoin('research_foci', 'research_foci.id', '=', 'teacher__research_foci.Research_focus_id')->
            select(
                'teachers.id',
                \DB::raw("CONCAT('teacher: ',name, ', code : ', code) AS label"),
                'code AS value',
                \DB::raw("CONCAT('[',GROUP_CONCAT(CASE WHEN research_foci.id IS NULL OR research_foci.Axes_and_themes_of_recherche IS NULL THEN '' ELSE JSON_OBJECT('label', COALESCE(research_foci.Axes_and_themes_of_recherche, '')) END), ']') AS Axes_and_themes_of_recherche")

                // \DB::raw(' false as  disabled'),
                // \DB::raw("CASE WHEN jury_members.specialty_id = $specialty_managed_id THEN group_number ELSE NULL END AS group_number")
            )
            ->where('teachers.code','!=',$supervisor_code)
            ->groupBy(
                'teachers.id',
                'label',
                'value',
                // 'Axes_and_themes_of_recherche'
            )

            ->get()->toArray()
        ;
    }


    public function fetchJuryMembersGroups(Request $request)
    {

        $teacher = $request->user();
        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;


        $response = [];

        $groups_numbers = jury_member::select('group_number')->where('specialty_id', $specialty_managed_id)
            ->groupBy('group_number')
            ->get()->toArray();

        foreach ($groups_numbers as $group_number) {

            $teachers = Teacher::select(
                \DB::raw(
                    \DB::raw("teachers.name AS teacher"),
                ),
                'isPresident',
            )
                ->leftJoin('jury_members', 'jury_members.teacher_id', '=', 'teachers.id')
                ->where('specialty_id', $specialty_managed_id)
                ->where('group_number', $group_number)
                ->get()->toArray();

            $label = json_encode($group_number['group_number']);

            $response[] = [
                'value' => $group_number['group_number'],
                'label' => "group number " . $label,
                'subLabel' => $teachers,
            ];

            $teachers = [];

        }


        return response($response, 200);





    }



    public function sendListOfLicenseJury(sendListOfLicenseJuryRequest $request)
    {
        $teacher = $request->user('teacher');

        $credentials = $request->validated();

        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        $i = 1;

        foreach ($credentials['groups'] as $group) {

            jury_member::where('group_number', '>=', $i)->where('specialty_id', $specialty_managed_id)->delete();
            foreach ($group as $teacher) {
                // * get teacher id
                if ($teacher_id = Teacher::select('id')->where('code', $teacher['code'])->get()->first()->id) {

                }
                // try {

                if (!empty($teacher['isPresident']) && $teacher['isPresident'] == 1) {
                    jury_member::create([
                        'teacher_id' => $teacher_id,
                        'specialty_id' => $specialty_managed_id,
                        'isPresident' => true,
                        'group_number' => $i
                    ]);
                } else {
                    jury_member::create([
                        'teacher_id' => $teacher_id,
                        'specialty_id' => $specialty_managed_id,
                        'isPresident' => false,
                        'group_number' => $i
                    ]);
                }


                // } catch (\Throwable $th) {
                //     continue;
                // }

            }
            $i = $i + 1;
        }


        return response('group inserted', 201);



    }


}
