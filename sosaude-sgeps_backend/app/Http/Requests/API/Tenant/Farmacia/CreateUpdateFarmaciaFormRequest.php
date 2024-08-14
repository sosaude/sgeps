<?php

namespace App\Http\Requests\API\Tenant\Farmacia;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateFarmaciaFormRequest extends FormRequest
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
            "nome" => 'required|string',
            "endereco" => 'required|string',
            "horario_funcionamento" => 'required|array',
            "activa" => 'required|boolean',
            "contactos" => 'required|array',
            "contactos.*" => 'min:2',
            "latitude" => 'required|numeric|between:-90.0,90.0',
            "longitude" => 'required|numeric|between:-180.0,180.0',
            "numero_alvara" => 'required',
            "data_alvara_emissao" => 'required|date',
            "observacoes" => 'nullable',
            'email' => [
                'nullable',
                'email',
                'email' => 'unique:farmacias,email,'.$id],
        ];
    }
}
