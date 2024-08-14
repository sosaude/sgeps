<?php

namespace App\Http\Requests\API\Tenant\Empresa;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;

class CreateUpdateBeneficiarioFormRequest extends FormRequest
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
        $empresa_id = Request::get('empresa_id');
        // $beneficiario_id = Request::get('beneficiario_id');
        // dd($beneficiario_id);
        
        return [
            // 'empresa_id' => 'required|integer',
            'utilizador_activo' => 'required|boolean',
            'nome' => 'required|string|max:100',
            'numero_identificacao' => 'nullable|string|max:50',
            'numero_beneficiario' => [
                'nullable',
                'string',
                'max:100',
                "unique:beneficiarios,numero_beneficiario,$id"
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('beneficiarios')->ignore($id)->where('empresa_id', $empresa_id)
            ],
            'endereco' => 'required|string|max:255',
            'bairro' => 'required|string|max:100',
            'telefone' => 'nullable|string|max:50',
            'genero' => ['required','string','min:1','max:1', Rule::in(['F', 'M'])],
            'data_nascimento' => 'required|date',
            'ocupacao' => 'required|string|max:100',
            'aposentado' => 'required|boolean',
            'doenca_cronica' => 'required|boolean',
            'doenca_cronica_nome' => 'required_if:doenca_cronica,1|array',
            'grupo_beneficiario_id' => 'required|integer',
            'tem_dependentes' => 'required|boolean',
            // 'dependentes.*.id' => $id != null ? 'required|integer' : 'nullable',
            'dependentes.*.id' => 'nullable|integer',
            'dependentes.*.utilizador_activo' => 'required|boolean',
            'dependentes.*.nome' => 'required|string|max:100',
            // 'dependentes.*.email' => [ 'nullable', 'email', Rule::unique('dependente_beneficiarios')->ignore($id, 'beneficiario_id')->where('empresa_id', $empresa_id)],
            // 'dependentes.*.email' => [ 'nullable', 'email', Rule::unique('dependente_beneficiarios')],
            'dependentes.*.numero_identificacao' => 'nullable|string|max:50',
            'dependentes.*.parantesco' => 'required|string|max:100',
            'dependentes.*.endereco' => 'required|string|max:255',
            'dependentes.*.bairro' => 'required|string|max:100',
            'dependentes.*.telefone' => 'nullable|string|max:50',
            'dependentes.*.genero' => ['required','string','min:1','max:1', Rule::in(['F', 'M'])],
            'dependentes.*.data_nascimento' => 'required|date',
            'dependentes.*.doenca_cronica' => 'required|boolean',
            'dependentes.*.doenca_cronica_nome' => 'required_if:dependentes.*.doenca_cronica,1|array',
        ];
    }
}
