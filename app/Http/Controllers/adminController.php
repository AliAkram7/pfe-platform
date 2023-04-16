<?php

namespace App\Http\Controllers;

use App\Models\Research_focus;
use App\Models\teacher_department_manager;
use App\Models\teacher_Research_focus;
use App\Models\Teacher_specialty_manager;
use Auth;
use Mail;
use App\Models\grade;
use App\Models\Teacher;
use App\Models\department;
use App\Models\Specialitie;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\SendEmailTeacher;
use App\Models\teacher_account_seeders;
use App\Http\Requests\addTeacherRequest;
use App\Http\Requests\updateTeacherAccountRequest;
use App\Notifications\EmailVerificationNotification;
use App\Notifications\TeacherEmailVerificationNotification;

class adminController extends Controller
{
    public function getDepartmentsInfo(Request $request)
    {

        $department_info = department::select('name', 'departments.id')
            ->leftJoin('teacher_department_managers', 'teacher_department_managers.id_department', '=', 'departments.id')->get();

        $response = [];
        foreach ($department_info as $department) {


            $speciality_info = Specialitie::select('specialities.id', 'specialities.fullname', 'specialities.abbreviated_name')
                ->where('department_id', $department->id)
                ->get();

            $response[] = [
                'department' => $department->name,
                'speciality_info' => $speciality_info,
            ];

        }


        return response($response, 200);
    }

    public function fetchTeachers(Request $request)
    {
        $list_accounts = $students = \DB::table('teacher_account_seeders as ta')

            ->leftJoin('teachers as t', 'ta.code', '=', 't.code')
            ->leftJoin('teacher_department_managers as tdm', 't.id', '=', 'tdm.id_teacher')
            ->leftJoin('teacher_specialty_managers as tsm', 't.id', '=', 'tsm.teacher_id')
            ->leftJoin('departments as d', 'd.id', '=', 'tdm.id_department')
            ->leftJoin('specialities as s', 'tsm.specialty_id', '=', 's.id')
            ->leftJoin('grades as g', 'g.id', '=', 't.grade_id')
            ->leftJoin('teacher__research_foci', 'teacher__research_foci.teacher_id', '=', 'ta.id')
            ->leftJoin('research_foci', 'research_foci.id', '=', 'teacher__research_foci.Research_focus_id')
            ->select(
                'ta.code',
                \DB::raw('ta.name'),
                'ta.institutional_email',
                'ta.account_status',
                'ta.logged',
                'ta.logged_at',
                'ta.grade',
                'tdm.id_department',
                'd.name as department_name',
                'tsm.specialty_id',
                's.fullname as specialty_name',
                'g.fullname as gradeName',
                'g.abbreviated_name',
                'teacher__research_foci.teacher_id' ,
                // \DB::raw("CONCAT('[', GROUP_CONCAT(JSON_OBJECT('value', research_foci.id, 'label', research_foci.Axes_and_themes_of_recherche)), ']') AS Axes_and_themes_of_recherche")
                \DB::raw("CONCAT('[', GROUP_CONCAT(CASE WHEN research_foci.id IS NULL OR research_foci.Axes_and_themes_of_recherche IS NULL THEN '' ELSE JSON_OBJECT('value', COALESCE(research_foci.id, ''), 'label', COALESCE(research_foci.Axes_and_themes_of_recherche, '')) END), ']') AS Axes_and_themes_of_recherche")


            )
            ->groupBy(
                'ta.id',
                'ta.code',
                \DB::raw('ta.name'),
                'ta.institutional_email',
                'ta.account_status',
                'ta.logged',
                'ta.logged_at',
                'ta.grade',
                'ta.grade',
                'tdm.id_department',
                'department_name',
                'tsm.specialty_id',
                'specialty_name',
                'gradeName',
                'g.abbreviated_name',
                'teacher__research_foci.teacher_id' ,
                // 'Axes_and_themes_of_recherche'
            )
            ->get()->toArray() ;

        return response(compact('list_accounts'), 200);
    }

    public function fetchGrades(Request $request)
    {
        return grade::select('fullname as label', 'id as value')->get();
    }
    public function fetchResearchFocus(Request $request)
    {
        return Research_focus::select('Axes_and_themes_of_recherche as label', 'id as value')->get();
    }


    public function fetchRoles(Request $request)
    {
        $departments_roles = department::select(
            \DB::raw("CONCAT('manage ',name) AS label"),

            \DB::raw("CONCAT(id,'D') AS value"),
            'name as group',
        )
            ->whereNotIn('departments.id', function ($query) {
                $query->select('id_department')->from('teacher_department_managers');
            })->get()->toArray();

        $specialty_roles = Specialitie::select()
            ->leftJoin('departments', 'departments.id', '=', 'specialities.department_id')
            ->select(
                'departments.name as group',
                \DB::raw("CONCAT('manage ',fullname, ' specialty') AS label"),
                \DB::raw("CONCAT(specialities.id,'S') AS value"),
            )
            ->whereNotIn('specialities.id', function ($query) {
                $query->select('specialty_id')->from('teacher_specialty_managers');
            })->get()->toArray();

        return array_merge($departments_roles, $specialty_roles);
    }


    // ! bdd Teacher operation
    // ! create teacher
    public function addTeacher(addTeacherRequest $request)
    {
        $cred = $request->validated();

        if (
            Teacher_account_seeders::create(
                [
                    'code' => $cred['code'],
                    'name' => $cred['name'],
                    'institutional_email' => $cred['institutional_email'],
                    'grade' => $cred['grade'],
                ]
            )

        ) {

            // $teacher->notify(new TeacherEmailVerificationNotification) ;

            $teacher = Teacher_account_seeders::select()->where('code', $cred['code'])->get()->first();

            $teacherPassword = Str::random(10);



            Teacher::create([
                'id' => $teacher['id'],
                'code' => $teacher['code'],
                'name' => $teacher['name'],
                'institutional_email' => $teacher['institutional_email'],
                'grade_id' => $teacher['grade'],
                'password' => bcrypt($teacherPassword),
            ]);


            Mail::to($teacher['institutional_email'])->send(new SendEmailTeacher($teacher['name'], $teacher['institutional_email'], $teacherPassword));

            return response('', 201);
        }




        //  $teacher =    Teacher::select()->where('code', $cred['code'])->get()->first() ;
        //     $teacher->notify(new TeacherEmailVerificationNotification) ;
        // return response('', 403);

    }

    // ! lock account

    public function lockAccount(updateTeacherAccountRequest $request)
    {
        $cred = $request->validated();
        $update = Teacher_account_seeders::
            where('code', $cred['code'])->
            update(['account_status' => 0]);
        return response('account locked', 201);
    }
    // ! unlock account
    public function unLockAccount(updateTeacherAccountRequest $request)
    {
        $cred = $request->validated();
        $update = Teacher_account_seeders::

            where('code', $cred['code'])->
            update(['account_status' => 1]);
        return response('account unlocked', 201);
    }
    // ! reset account
    public function resetAccount(updateTeacherAccountRequest $request)
    {
        $cred = $request->validated();

        Teacher::where('code', $cred['code'])->delete();

        Teacher_account_seeders::
            where('code', $cred['code'])->
            update(
                ['account_status' => 0, 'logged' => 0]
            );
        $teacher = Teacher_account_seeders::select()->where('code', $cred['code'])->get()->first();
        $password = Str::random(10);

        Teacher::create([
            'id' => $teacher['id'],
            'code' => $teacher['code'],
            'name' => $teacher['name'],
            'institutional_email' => $teacher['institutional_email'],
            'grade_id' => $teacher['grade'],
            // 'password' => bcrypt($password),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ]);


        // Mail::to($teacher['institutional_email'])->send(new SendEmailTeacher($teacher['name'], $teacher['code'], $teacher['institutional_email'], $password));

        return response('account  restored', 201);
    }

    public function deleteAccount(updateTeacherAccountRequest $request)
    {
        $cred = $request->validated();
        teacher::where('code', $cred['code'])->delete();
        Teacher_account_seeders::
            where('code', $cred['code'])->delete();
        return response('account deleted', 201);
    }

    // ! update account


    public function updateTeacherAccount(updateTeacherAccountRequest $request)
    {
        $cred = $request->validated();

        if (!empty($cred['name'])) {
            Teacher_account_seeders::
                where('code', $cred['code'])->
                update(['name' => $cred['name']]);
            Teacher::
                where('code', $cred['code'])->
                update(['name' => $cred['name']]);
        }
        if (!empty($cred['updated_code'])) {
            Teacher_account_seeders::
                where('code', $cred['code'])->
                update(['code' => $cred['updated_code']]);
            Teacher::
                where('code', $cred['code'])->
                update(['code' => $cred['updated_code']]);

        }
        if (!empty($cred['institutional_email'])) {
            Teacher_account_seeders::
                where('code', $cred['code'])->
                update(['institutional_email' => $cred['institutional_email']]);
            Teacher::
                where('code', $cred['code'])->
                update(['institutional_email' => $cred['institutional_email']]);
        }
        if (!empty($cred['sGrade'])) {
            Teacher_account_seeders::
                where('code', $cred['code'])->
                update(['grade' => $cred['sGrade']]);
            Teacher::
                where('code', $cred['code'])->
                update(['grade_id' => $cred['sGrade']]);
        }

        if (!empty($cred['SSearchFoci']) && count($cred['SSearchFoci']) > 0 ) {
            $teacher_id = Teacher_account_seeders::select('id')
                ->where('code', $cred['code'])
                ->get()
                ->first()->id;
            teacher_Research_focus::where('teacher_id', $teacher_id)->delete();
            foreach ($cred['SSearchFoci'] as $research_id) {
                // try {
                    teacher_Research_focus::create([
                        'teacher_id' => $teacher_id,
                        'Research_focus_id' => $research_id
                    ]);
                // } catch (\Throwable $th) {
                //     continue;
                // }
            }
        }



        if (!empty($cred['sRole']['id'])) {

            $teacher_id = Teacher_account_seeders::
                where('code', $cred['code'])->get()->first()->id;

            if (teacher_department_manager::where('id_teacher', $teacher_id)->get() != null) {
                teacher_department_manager::where('id_teacher', $teacher_id)->delete();
            }

            if (Teacher_specialty_manager::where('teacher_id', $teacher_id)->get() != null) {
                Teacher_specialty_manager::where('teacher_id', $teacher_id)->delete();
            }



            if ($cred['sRole']['type'] === 'S') {
                Teacher_specialty_manager::create([
                    'teacher_id' => $teacher_id,
                    'specialty_id' => $cred['sRole']['id'],
                ]);

            }
            if ($cred['sRole']['type'] === 'D') {

                teacher_department_manager::create([
                    'id_teacher' => $teacher_id,
                    'id_department' => $cred['sRole']['id'],
                ]);
            }

        }







        return response('account updated', 201);
    }





}
