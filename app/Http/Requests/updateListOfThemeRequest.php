<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateListOfThemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        // ! add condition to this request
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
            'theme_list' => 'required'
        ];
    }
}
