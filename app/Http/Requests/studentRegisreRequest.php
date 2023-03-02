<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class studentRegisreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
                'code' =>  'required | min:8 |numeric|unique:students,code',
                'email'=>'email|unique:students,email',
                'name'=>"required|min:3,max:32",
                'tel'=>"numeric|nullable",
                'password'=>'required',
                Password::min(8)->letters(),
        ];
    }
}
