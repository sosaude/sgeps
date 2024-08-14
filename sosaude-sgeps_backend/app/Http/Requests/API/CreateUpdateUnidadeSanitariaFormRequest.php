<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateUnidadeSanitariaFormRequest extends FormRequest
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
            'categoria_unidade_sanitaria_id' => 'required|integer',
            'nome' => 'required|string',
            'endereco' => 'required|string',
            'contactos' => 'required|array',
            'contactos.*' => 'min:2',
            'nuit' => 'required|integer',
            'email' => 'required|email',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'email' => [
                'nullable',
                'email',
                'email' => 'unique:unidade_sanitarias,email,'.$id],
        ];
    }
}
