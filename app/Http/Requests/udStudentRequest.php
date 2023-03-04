<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
class udStudentRequest extends FormRequest
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
            'code' =>'numeric|required',
            'updated_code'=>'numeric|min:8|unique:students_account_seeders,code',
            'name'=>'string',
            'default_password'=>'string|min:8', Password::min(8)->letters()->numbers()->mixedCase()
        ];
    }
}
