<?php

namespace App\Http\Requests\API;

use App\Models\Clinica;
use InfyOm\Generator\Request\APIRequest;

class CreateClinicaAPIRequest extends APIRequest
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
        $rules_store = ['email' => 'unique:clinicas,email'];
        $rules = Clinica::$rules;

        return array_merge($rules, $rules_store);
    }
}
