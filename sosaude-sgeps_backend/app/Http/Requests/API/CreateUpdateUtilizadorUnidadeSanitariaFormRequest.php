<?php

namespace App\Http\Requests\API;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Request;
use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateUtilizadorUnidadeSanitariaFormRequest extends FormRequest
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
        $unidade_sanitaria_id = Request::get('unidade_sanitaria_id');

        return [
            'nome' => 'required|string|max:100',
            'contacto' => 'nullable|string|max:100',
            'activo' => 'required|boolean',
            'nacionalidade' => 'required|string|max:100',
            'observacoes' => 'nullable|string|max:255',
            'unidade_sanitaria_id' => 'required|integer|exists:unidade_sanitarias,id',
            'role_id' => 'required|integer|exists:roles,id',
            'permissaos' => 'nullable|array',
            'permissaos.*' => 'nullable|integer|exists:permissaos,id',
            'email' => [
                'nullable',
                'email',
                Rule::unique('utilizador_unidade_sanitarias')->ignore($id)->where('unidade_sanitaria_id', $unidade_sanitaria_id)],
        ];
    }
}
