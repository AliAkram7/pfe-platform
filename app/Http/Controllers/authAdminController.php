<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\createAdminRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use \Auth;

class authAdminController extends Controller
{
    public function createAdmin(createAdminRequest $request)
    {
        $credentials = $request->validated();

        Admin::create([
            'email' => $credentials['email'],
            'password' => bcrypt($credentials['password']),
        ]);
        return response('admin created', 201);
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->validated();

        $token  = Auth::guard('admin') ->attempt($credentials);
        if (!$token) {
            return response(['message' => 'bad cred'], 401);
        }
        $user = Auth::guard('admin')->user();
        $role = 'admin' ;
        return response(compact('user','token', 'role'));


    }

}
