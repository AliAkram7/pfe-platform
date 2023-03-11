<?php

namespace App\Http\Controllers;

use App\Http\Requests\addRankByStudent;
use App\Http\Requests\deleteRankRequest;
use App\Http\Requests\updateStudentRankRequest;
use App\Http\Requests\uploadRaknsRequest;
use App\Models\Rank;
use App\Models\Student_speciality;
use App\Models\Teacher_specialty_manager;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RankConroller extends Controller
{
    public function getStudentWithoutRank(Request $request)
    {
        // TODO get the student those haven't rank
        $teacher = $request->user('teacher');

        $specialty_id = Teacher_specialty_manager::get()->where('teacher_id', $teacher->id)->first()->specialty_id;



        try {
            $student_without_rank = \DB::table('students')
                ->select(
                    // \DB::raw("CONCAT('student: ',name, ' with code : ', code) AS value"),
                    'code AS value',
                    \DB::raw("CONCAT('student: ',name, ' with code : ', code) AS label"),
                    'code AS description'
                )
                ->join('student_specialities', 'students.id', '=', 'student_specialities.student_id')
                ->leftJoin('ranks', function ($join) {
                    $join->on('student_specialities.id', '=', 'ranks.student_specialite_id');
                })
                ->where('student_specialities.speciality_id', '=', $specialty_id)
                ->whereNull('ranks.id')
                ->get();
        } catch (\Throwable $th) {
            return response('error in rank controller line 30');
        }
        return response(compact('student_without_rank'), 200);
    }


    public function addRankByStudent(addRankByStudent $request)
    {

        // 'code' => 'numeric|required',
        // 'ms1' => 'numeric|min:0|max:20|required',
        // 'ms2' => 'numeric|min:0|max:20|required',
        // 'mgc' => 'numeric|min:0|max:20|required',
        // 'obs' => 'numeric|min:1|max:4 '

        $credentials = $request->validated();



        // ! get student_specialty_id
        // ! use me later
        $student_speciality_id = Student_speciality::select('student_specialities.id')
            ->leftJoin('specialities', 'specialities.id', '=', 'student_specialities.speciality_id')
            ->leftJoin('students', 'students.id', '=', 'student_specialities.student_id')
            ->where('code', $credentials['code'])
            ->get()->first()->id;


        Rank::create([
            'student_specialite_id' => $student_speciality_id,
            'ms1' => $credentials['ms1'],
            'ms2' => $credentials['ms2'],
            'mgc' => $credentials['mgc'],
            'observation' => $credentials['obs'],
        ]);
    }

    public function uploadRanks(uploadRaknsRequest $request)
    {

        $cred = $request->validated();

        $file = $request->file('file');

        $spreadsheet = IOFactory::load($file->getPathname());

        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();

        for ($row = 2; $row <= $highestRow; $row++) {



            if (
                $student_speciality_id = Student_speciality::select('student_specialities.id')
                    ->leftJoin('specialities', 'specialities.id', '=', 'student_specialities.speciality_id')
                    ->leftJoin('students', 'students.id', '=', 'student_specialities.student_id')
                    ->where('code', $worksheet->getCell('A' . $row)->getValue())
                    ->get()->first()
            ) {
                $data = [
                    'student_specialite_id' => $student_speciality_id->id,
                    'ms1' => $worksheet->getCell('B' . $row)->getValue(),
                    'ms2' => $worksheet->getCell('C' . $row)->getValue(),
                    'mgc' => $worksheet->getCell('D' . $row)->getValue(),
                    'observation' => $worksheet->getCell('E' . $row)->getValue(),

                ];
            }
            try {

                \DB::table('ranks')->insert($data);

            } catch (\Throwable $th) {
                continue;
            }

        }
    }

    public function deleteRank(deleteRankRequest $request)
    {
        $credentials = $request->validated();

        if (
            $student_speciality_id = Student_speciality::select('student_specialities.id')
                ->leftJoin('specialities', 'specialities.id', '=', 'student_specialities.speciality_id')
                ->leftJoin('students', 'students.id', '=', 'student_specialities.student_id')
                ->where('code', $credentials['code'])
                ->get()->first()
        ) {
            \DB::delete('delete from ranks where student_specialite_id  = ?  ', [$student_speciality_id->id]);

        }

    }


    public function updateRank(updateStudentRankRequest $request)
    {
        $credentials = $request->validated();
        if (
            !$student_speciality_id = Student_speciality::select('student_specialities.id')
                ->leftJoin('specialities', 'specialities.id', '=', 'student_specialities.speciality_id')
                ->leftJoin('students', 'students.id', '=', 'student_specialities.student_id')
                ->where('code', $credentials['code'])
                ->get()->first()
        ) {
            return response('students not found', 500);
        }
        if (!empty($credentials['ms1'])) {
            \DB::update('update ranks  set ms1 = ?  where student_specialite_id = ?', [$credentials['ms1'], $student_speciality_id->id]);
        }
        if (!empty($credentials['ms2'])) {
            \DB::update('update ranks  set ms2 = ?  where student_specialite_id= ?', [$credentials['ms2'], $student_speciality_id->id]);
        }
        if (!empty($credentials['mgc'])) {
            \DB::update('update ranks  set mgc = ?  where student_specialite_id = ?', [$credentials['mgc'], $student_speciality_id->id]);
        }
        if (!empty($credentials['obs'])) {
            \DB::update('update ranks  set obs = ?  where student_specialite_id = ?', [$credentials['obs'], $student_speciality_id->id]);
        }
    }



}
