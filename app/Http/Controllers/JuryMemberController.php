<?php

namespace App\Http\Controllers;

use App\Http\Requests\sendListOfLicenseJuryRequest;
use App\Models\jury_member;
use App\Models\Teacher;
use App\Models\Teacher_specialty_manager;
use Illuminate\Http\Request;

class JuryMemberController extends Controller
{
    public function fetchTeachers(Request $request)
    {
        $teacher = $request->user('teacher');
        $specialty_managed_id = Teacher_specialty_manager::select('specialty_id')->where('teacher_id', $teacher->id)->get()->first()->specialty_id;

        return Teacher::
            leftJoin('jury_members', 'jury_members.teacher_id', '=', 'teachers.id')
            ->select(
                'teachers.id',
                \DB::raw("CONCAT('teacher: ',name, ' with code : ', code) AS label"),
                'code AS value',
                // \DB::raw(' false as  disabled'),
                \DB::raw("CASE WHEN jury_members.specialty_id = $specialty_managed_id THEN group_number ELSE NULL END AS group_number")
            )
            ->distinct()
            ->get()

        ;
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
                try {
                    jury_member::create([
                        'teacher_id' => $teacher_id,
                        'specialty_id' => $specialty_managed_id,
                        'group_number' => $i
                    ]);
                } catch (\Throwable $th) {
                    continue;
                }

            }
            $i = $i + 1;
        }


        return response('group inserted', 201);



    }


}
