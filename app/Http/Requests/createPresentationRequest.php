<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class createPresentationRequest extends FormRequest
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
            'team_id' => 'required' ,
            'dateApp' => 'required|date',
            'dateTest' => 'required|date',
            'president' => 'numeric|required',
            'ex1' => 'required|numeric',
            'ex2' => 'required|numeric',
            'ex3' => 'required|numeric',
            'tester_1' => 'required|numeric',
            'tester_2' => 'required|numeric',
        ];
    }
}
