<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class teacherUpdateInfoRequest extends FormRequest
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
            'personal_email' => "email|nullable",
            'institutional_email' => "email|nullable",
            'tel' => "numeric|nullable",
            "prPassword" => "required",
            "newPassword" => "nullable", Password::min(8)->letters(),
        ];
    }
}
