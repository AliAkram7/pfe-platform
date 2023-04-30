<?php

namespace App\Http\Controllers;

use App\Http\Requests\addStudentRequest;
use App\Http\Requests\udStudentRequest;
use App\Http\Requests\UploadSeederRequest;
use App\Models\department;
use App\Models\Student;
use App\Models\Students_Account_Seeder;

use App\Models\Student_speciality;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;


class DepartmentManagerController extends Controller
{
    // !! in any of this function check if the request come from department manager

    public function getDepartmentInfo(Request $request)
    {
        // if (JWTAuth::parseToken()->getPayload()['department_manager'] == 0) {
        //     return response('not allowed', 403);
        // }
        $department_manager_id = $request->user('teacher')->id;
        $speciality_info = department::select('specialities.id', 'specialities.fullname', 'specialities.abbreviated_name')
            ->where('teacher_department_managers.id_teacher', $department_manager_id)
            ->leftJoin('teacher_department_managers', 'teacher_department_managers.id_department', '=', 'departments.id')
            ->join('specialities', 'department_id', '=', 'departments.id')
            ->get();

        $department_info = department::select('name')
            ->where('teacher_department_managers.id_teacher', $department_manager_id)
            ->leftJoin('teacher_department_managers', 'teacher_department_managers.id_department', '=', 'departments.id')->get()->first();
        $response = [
            'department_name' => $department_info,
            'speciality_info' => $speciality_info,
        ];

        return response($response, 200);
    }


    public function fetchStudentsData(Request $request, $id, $year_id)
    {

        // echo $id ;
        // return  $year_id ;

        $list_accounts = $students = \DB::table('students_account_seeders as sa')
            ->leftJoin('students as s', 'sa.code', '=', 's.code')
            ->leftJoin('student_specialities', 'student_specialities.student_id', '=', 'sa.id')
            // ->leftJoin('year_scholars', 'year_scholars.id', '=', 'student_specialities.year_scholars_id')
            ->select(
                'sa.code',
                \DB::raw('sa.name'),
                'sa.default_password',
                's.email',
                's.tel',
                'sa.account_status',
                'sa.logged',
                'sa.logged_at',
                // 'sa.specialty_id',
            )
            ->union(\DB::table('students as s')
                ->join('students_account_seeders as sa', 's.code', '=', 'sa.code')
                ->select(
                    's.code',
                    'sa.default_password',
                    's.name',
                    's.email',
                    's.tel',
                    'sa.account_status',
                    'sa.logged',
                    'sa.logged_at',
                    // 'sa.specialty_id',
                )
                ->whereNotIn('s.code', function ($query) {
                    $query->select('code')->from('students_account_seeders');
                }))
            ->where('student_specialities.speciality_id', $id)
            ->where('year_scholar_id', $year_id)
            ->get();
        return response(compact('list_accounts'), 200);
    }

    public function upload(UploadSeederRequest $request)
    {

        $cred = $request->validated();

        $file = $request->file('file');

        $spreadsheet = IOFactory::load($file->getPathname());

        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();

        for ($row = 2; $row < $highestRow; $row++) {

            $student = Students_Account_Seeder::select()->where('code', $worksheet->getCell('A' . $row)->getValue())->get()->first();

            if ($student == null) {
                if (
                    $user = Students_Account_Seeder::create(
                        [
                            'code' => $worksheet->getCell('A' . $row)->getValue(),
                            'name' => $worksheet->getCell('B' . $row)->getValue(),
                            'default_password' => Str::random(10),
                        ]
                    )
                ) {
                    Student_speciality::create(
                        [
                            'student_id' => $user->id,
                            'speciality_id' => $cred['specialty_id'],
                            'year_scholar_id' => $cred['yearId']
                        ]
                    );
                }
            } else {
                Student_speciality::create(
                    [
                        'student_id' => $student->id,
                        'speciality_id' => $cred['specialty_id'],
                        'year_scholar_id' => $cred['yearId']
                    ]
                );
            }
        }
    }

    public function addStudent(addStudentRequest $request)
    {
        $cred = $request->validated();

        $student = Students_Account_Seeder::select()->where('code', $cred['code'])->get()->first();

        if ($student == null) {
            if (
                $user = Students_Account_Seeder::create(
                    [
                        'code' => $cred['code'],
                        'name' => $cred['name'],
                        'default_password' => Str::random(10),
                    ]
                )

            ) {

                Student_speciality::create(
                    [
                        'student_id' => $user->id,
                        'speciality_id' => $cred['specialty_id'],
                        'year_scholar_id' => $cred['yearId']
                    ]
                );
                return response('', 201);
            }


        } else {

            Student_speciality::create(
                [
                    'student_id' => $student->id,
                    'speciality_id' => $cred['specialty_id'],
                    'year_scholar_id' => $cred['yearId']
                ]
            );
        }

        return response('', 403);

    }

    // ! bdd student operation
    // ! lock account

    public function lockAccount(udStudentRequest $request)
    {
        $cred = $request->validated();
        $update = Students_Account_Seeder::
            where('code', $cred['code'])->
            update(['account_status' => 0]);
        return response('account locked', 201);
    }
    // ! unlock account
    public function unLockAccount(udStudentRequest $request)
    {
        $cred = $request->validated();
        $update = Students_Account_Seeder::

            where('code', $cred['code'])->
            update(['account_status' => 1]);
        return response('account unlocked', 201);
    }
    // ! delete account
    public function deleteAccount(udStudentRequest $request)
    {
        $cred = $request->validated();
        Student::where('code', $cred['code'])->delete();
        Students_Account_Seeder::where('code', $cred['code'])->delete();
        return response('account deleted', 201);
    }

    // ! reset account
    public function resetStudentAccount(udStudentRequest $request)
    {
        $cred = $request->validated();
        Student::where('code', $cred['code'])->delete();
        Students_Account_Seeder::
            where('code', $cred['code'])->
            update(['account_status' => 0, 'logged' => 0]);
        return response('account deleted', 201);
    }


    // ! update account

    public function updateAccount(udStudentRequest $request)
    {
        $cred = $request->validated();

        if (!empty($cred['name'])) {
            Students_Account_Seeder::
                where('code', $cred['code'])->
                update(['name' => $cred['name']]);
            Student::
                where('code', $cred['code'])->
                update(['name' => $cred['name']]);



        }
        if (!empty($cred['updated_code'])) {
            Students_Account_Seeder::
                where('code', $cred['code'])->
                update(['code' => $cred['updated_code']]);


            Student::
                where('code', $cred['code'])->
                update(['code' => $cred['updated_code']]);

        }
        if (!empty($cred['default_password'])) {
            Students_Account_Seeder::
                where('code', $cred['code'])->
                update(['default_password' => $cred['default_password']]);
        }
        return response('account updated', 201);
    }

}
