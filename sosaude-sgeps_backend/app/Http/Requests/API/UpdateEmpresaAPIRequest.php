<?php

namespace App\Http\Requests\API;

use App\Models\Empresa;
use InfyOm\Generator\Request\APIRequest;

class UpdateEmpresaAPIRequest extends APIRequest
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
        
        $rules_store = ['email' => 'unique:empresas,email,'.$id];
        $rules = Empresa::$rules;

        return array_merge($rules, $rules_store);
    }
}
