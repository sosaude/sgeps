<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;

class CreateUpdateUtilizadorEmpresaFormRequest extends FormRequest
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

        return [
            'nome' => 'required|string|max:100',
            'empresa_id' => 'required|integer|exists:empresas,id',
            'activo' => 'required|boolean',
            'role_id' => 'required|integer|exists:roles,id',
            'nacionalidade' => 'required|string|max:100',
            'observacoes' => 'nullable|string|max:255',
            'permissaos' => 'nullable|array',
            'permissaos.*' => 'nullable|integer|exists:permissaos,id',
            'email' => [
                'nullable',
                'email',
                Rule::unique('utilizador_empresas')->ignore($id)->where('empresa_id', $empresa_id)
            ],
        ];
    }
}
