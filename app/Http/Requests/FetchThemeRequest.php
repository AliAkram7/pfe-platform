<?php

namespace App\Http\Requests;

use App\Models\Student_speciality;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class FetchThemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(Request $request)
    {
        // //  !! get students specialty
        // $student = $request->user() ;
        // $specialty_id = Student_speciality::select()->where('student_id', $student->id)->get()->first()->speciality_id ;

        // $check = env("SPECIALTY_".$specialty_id."_PUBLISH_THEME") == true;

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
