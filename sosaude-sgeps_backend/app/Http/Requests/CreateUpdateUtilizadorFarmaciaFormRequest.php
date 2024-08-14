<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Request;
use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateUtilizadorFarmaciaFormRequest extends FormRequest
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
        $farmacia_id = Request::get('farmacia_id');

        return [
            'nome' => 'required|string|max:100',
            'contacto' => 'nullable|string',
            'numero_caderneta' => 'nullable|integer',
            'activo' => 'required|boolean',
            'categoria_profissional' => 'nullable|string|max:100',
            'nacionalidade' => 'required|string|max:100',
            'observacoes' => 'nullable|string|max:255',
            'farmacia_id' => 'required|integer|exists:farmacias,id',            
            'role_id' => 'required|integer|exists:roles,id',
            'permissaos' => 'nullable|array',
            'permissaos.*' => 'nullable|integer|exists:permissaos,id',
            // 'email' => "unique:utilizador_farmacias,email,$id,id,farmacia_id,$farmacia_id"
            'email' => [
                'nullable',
                'email',
                // "unique:utilizador_farmacias,email,$id,id,farmacia_id,$farmacia_id"],
                // 'email' => 'unique:utilizador_farmacias,email,NULL,id,farmacia_id,'.$farmacia_id],
                Rule::unique('utilizador_farmacias')->ignore($id)->where('farmacia_id', $farmacia_id)],
            
        ];

        // $create_rules = [

        // ]
    }
}
