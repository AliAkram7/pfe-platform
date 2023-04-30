<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class addRankByStudent extends FormRequest
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
            'yearId' => 'numeric|required',
            'code' => 'numeric|required',
            'ms1' => 'numeric|min:0|max:20|required',
            'ms2' => 'numeric|min:0|max:20|required',
            'mgc' => 'numeric|min:0|max:20|required',
            'obs' => 'numeric|min:1|max:4 '
        ];
    }
}
