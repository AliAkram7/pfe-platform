<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Students_Account_Seeder;
use App\Models\Student_speciality;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use Auth;
use App\Http\Requests\studentLoginRequest;
use App\Http\Requests\studentRegisreRequest;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthStudentsController extends Controller
{
    public function registre(studentRegisreRequest $request)
    {
        $fieldes = $request->validated();


        try {
            $user = Student::create(
                [
                    'code' => $fieldes['code'],
                    'name' => $fieldes['name'],
                    'email' => $fieldes["email"],
                    "password" => bcrypt($fieldes['password']),
                    "tel" => $fieldes['tel'] ? $fieldes['tel'] : null,
                ]
            );
            $token = $user->createToken("main")->plainTextToken;

            $response = [
                $user,
                $token,
            ];

            return response($response, 201);

        } catch (\ErrorException $th) {
            return response('', 201);
        }

    }
    public function logout(Request $request)
    {

        $token = $request->header('auth-token');

        if ($token == null) {
            return response('error', 401);
        }
        JWTAuth::setToken($token)->invalidate();
        return response('you logout ', 200);

    }



    public function login(studentLoginRequest $request)
    {

        $credentials = $request->only(['code', 'password']);


        $account_status = Students_Account_Seeder::select('logged')->where('code', $credentials['code'])->get()->first();


        if ($account_status->logged == 0) {

            $default_password = Students_Account_Seeder::select('default_password')->where('code', $credentials['code'])->get()->first();
            if ($credentials['password'] === $default_password->default_password) {
                $studentInformation = Students_Account_Seeder::select('*')->where('code', $credentials['code'])->get()->first();
                try {
                    $user = Student::create(
                        [
                            'code' => $studentInformation['code'],
                            'name' => $studentInformation['name'],
                            'email' => null,
                            "password" => bcrypt($default_password->default_password),
                            "tel" => null,
                        ]
                    );

                    
                Student_speciality::create(
                    [
                    'student_id'=>$user->id,
                    'speciality_id'=>$studentInformation['specialty_id'],
                    'year_scholar'=>date('Y')
                    ]
                    ) ;

                    $token = Auth::guard('student')->attempt($credentials);
                    if (!$token) {
                        return response(['message' => 'bad cred2'], 401);
                    }
                    $user = Auth::guard('student')->user();
                    $account_status = Students_Account_Seeder::select('account_status')->where('code', $user->code)->get()->first();
                    if ($account_status->account_status == 0) {
                        return response(["message" => 'Unauthorized'], 401);
                    }
                    $role = 'student';
                    return response(compact('user', 'token', 'role'));



                } catch (\Illuminate\Database\QueryException $th) {


                    $token = Auth::guard('student')->attempt($credentials);
                    if (!$token) {
                        return response(['message' => 'bad cred'], 401);
                    }
                    $user = Auth::guard('student')->user();
                    $account_status = Students_Account_Seeder::select('account_status')->where('code', $user->code)->get()->first();
                    if ($account_status->account_status == 0) {
                        return response(["message" => 'Unauthorized'], 401);
                    }
                    $role = 'student';
                    return response(compact('user', 'token', 'role'));

                }


            } else {
                return response(['message' => 'bad cred'], 401);
            }
        } else {
            $token = Auth::guard('student')->attempt($credentials);
            if (!$token) {
                return response(['message' => 'bad cred'], 401);
            }
            $user = Auth::guard('student')->user();
            $account_status = Students_Account_Seeder::select('account_status')->where('code', $user->code)->get()->first();
            if ($account_status->account_status == 0) {
                return response(["message" => 'Unauthorized'], 401);
            }
            $role = 'student';
            return response(compact('user', 'token', 'role'));
        }

    }



}
