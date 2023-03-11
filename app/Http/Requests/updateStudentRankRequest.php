<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateStudentRankRequest extends FormRequest
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
            'code' => 'numeric|required' ,
            'ms1'=> 'nullable|min:0|max:20',
            'ms2'=> 'nullable|min:0|max:20',
            'mgc'=> 'nullable|min:0|max:20',
            'obs'=>'nullable|min:1|max:4'
        ];
    }
}
