<?php

namespace App\Http\Requests\API;

use App\Models\Empresa;
use InfyOm\Generator\Request\APIRequest;

class CreateEmpresaAPIRequest extends APIRequest
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

        $rules_store = ['email' => 'unique:empresas,email'];
        $rules = Empresa::$rules;

        return array_merge($rules, $rules_store);
    }
}
