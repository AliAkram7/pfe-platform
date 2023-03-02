<?php

namespace App\Http\Controllers;

use App\Models\Student;
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
        $token = Auth::guard('student')->attempt($credentials);
        if (!$token) {
            return response(['message' => 'bad cred'], 401);
        }

        $user = Auth::guard('student')->user();

        $role = 'student' ;

        return response(compact('user', 'token', 'role'));
    }

}
