<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherLoginRequest;
use App\Models\teacher_account_seeders;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use \Auth;

class AuthTeacherController extends Controller
{


    public function logout(Request $request)
    {
        $token = $request->header('auth-token');
        if ($token == null) {
            return response('error', 401);
        }
        JWTAuth::setToken($token)->invalidate();
        return response('', 200);

    }



    public function login(TeacherLoginRequest $request)
    {

        $cred = $request->validated() ;

        if (empty($cred['institutional_email'])) {
            $credentials = $request->only(['code', 'password']);
        } else {
            $credentials = $request->only(['institutional_email', 'password']);
        }




        if (


            (!empty($credentials['code']) && Teacher_account_seeders::select('account_status')
                ->where('code', $credentials['code'])
                ->get()
                ->first()['account_status'] == false)

            ||

            (!empty($credentials['institutional_email']) &&
                Teacher_account_seeders::select('account_status')
                    ->where('institutional_email', $credentials['institutional_email'])
                    ->get()
                    ->first()['account_status'] == false)
        ) {
            return response(['message' => 'Unauthorized'], 401);

        }
        $token = Auth::guard('teacher')->attempt($credentials);
        if (!$token) {
            return response(['message' => 'bad cred'], 401);
        }
        $user = Auth::guard('teacher')->user();


        $role = 'teacher';
        return response(compact('user', 'token', 'role'));


    }




}
