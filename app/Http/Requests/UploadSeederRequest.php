<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadSeederRequest extends FormRequest
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
            'specialty_id' => 'required|numeric|exists:specialities,id',
            'yearId' =>'required|numeric|exists:year_scholars,id',
            'file'=> 'file'
        ];
    }
}
