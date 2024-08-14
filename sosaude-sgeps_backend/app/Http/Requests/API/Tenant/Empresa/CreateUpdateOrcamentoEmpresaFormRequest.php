<?php

namespace App\Http\Requests\API\Tenant\Empresa;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class CreateUpdateOrcamentoEmpresaFormRequest extends FormRequest
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
        
        
        return [
            'tipo_orcamento' => 'required|string|max:255',
            'orcamento_laboratorio' => 'required|numeric',
            'orcamento_farmacia' => 'required|numeric',
            'orcamento_clinica' => 'required|numeric',
            'executado' => 'required|boolean',
            'ano_de_referencia' => 'required|string|max:255'
        ];
    }
}
