<?php

namespace App\Http\Controllers\SESSEION;

use App\Http\Controllers\Controller;
use App\Http\Requests\teacherUpdateInfoRequest;
use Illuminate\Http\Request;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class sessionTeacherController extends Controller
{
    public function getTeacherInfo(Request $request)
    {
        $role = "teacher";
        $user = $request->user('teacher');
        return response(compact('user', 'role'));

    }

    public function teacherUpdateInfo(teacherUpdateInfoRequest $request)
{

    $credentials = $request->validated();
    $teacher = Auth::guard('teacher')->user();

    if (!$teacher || !Hash::check($credentials['prPassword'], $teacher->password)) {
        return response('bad cred', 403);
    }
    if (!empty($credentials['newPassword'])) {
        $teacher->update(['password' => bcrypt($credentials['newPassword'])]);
    }
    if ($teacher->update($request->all())) {
        return response('updated', 201);
    } else {
        return response('', 204);
    }

}
public function refreshToken(Request $request)
{
    $token = Auth::guard('teacher')->refresh();

    return response(compact('token'), 200);

}
}




