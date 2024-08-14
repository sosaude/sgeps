<?php

namespace App\Http\Requests\API;

use App\Models\Clinica;
use InfyOm\Generator\Request\APIRequest;

class UpdateClinicaAPIRequest extends APIRequest
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

        $id = $this->route('id');

        $rules_update = ['email' => 'unique:clinicas,email,' . $id];
        $rules = Clinica::$rules;

        return array_merge($rules, $rules_update);
    }
}
