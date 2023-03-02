<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherLoginRequest;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use \Auth;
class AuthTeacherController extends Controller
{


    public function logout(Request $request)
    {
        $token =  $request->header('auth-token');
        if ($token == null) {
            return response('error', 401);
        }
        JWTAuth::setToken($token)->invalidate();
        return response('', 200);

    }



    public function login(TeacherLoginRequest $request)
    {
        $credentials = $request->only(['code', 'password']);
        $token  = Auth::guard('teacher') ->attempt($credentials);
        if (!$token) {
            return response(['message' => 'bad cred'], 401);
        }
        $user = Auth::guard('teacher')->user();
        $role = 'teacher' ;
        return response(compact('user','token', 'role'));
    }

}
