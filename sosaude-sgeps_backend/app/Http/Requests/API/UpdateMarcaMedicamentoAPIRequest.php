<?php

namespace App\Http\Requests\API;

use Illuminate\Http\Request;
use App\Models\MarcaMedicamento;
use InfyOm\Generator\Request\APIRequest;

class UpdateMarcaMedicamentoAPIRequest extends APIRequest
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
        $medicamento_id = Request::get('medicamento_id');

        $rules = MarcaMedicamento::$rules;
        $update_rules = [
            'codigo' => 'required|string',
            'marca' => 'unique:marca_medicamentos,marca,'.$this->id.',id,medicamento_id,' . $medicamento_id,
        ];
        
        return array_merge($rules, $update_rules);
    }
}
