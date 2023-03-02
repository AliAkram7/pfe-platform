<?php

namespace App\Http\Controllers;

use App\Http\Requests\registreRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class authUser extends Controller
{
    public function registre(registreRequest $request)
    {
        $fields = $request->validated();

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);


        $token = $user->createToken("user")->plainTextToken;

        $response = [
            $user,
            $token
        ];

        return response($response, 201);


    }


    public function logout(Request $request )
    {
        auth()->user()->tokens()->delete();
        return response('', 204);

    }


}
