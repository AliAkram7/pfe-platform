<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThemeSuggestionRequest extends FormRequest
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
            'title' => 'string|required',
            'specialty' => 'string|required',
            'searchDomain' => 'required|array',
            'description' => 'required|string',
            'objectives' => 'required|string',
            'keyWords' => 'required|array',
            'workPlan' => 'required|array'
        ];
    }
}
