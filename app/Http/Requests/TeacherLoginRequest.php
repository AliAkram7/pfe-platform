<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'code' => 'exists:teacher_account_seeders,code|min:8|nullable',
            'institutional_email'=>'email|nullable|exists:teacher_account_seeders,institutional_email' ,
            'password' =>'required|min:8'
        ];
    }
}
