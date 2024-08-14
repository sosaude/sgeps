<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateEmpresaFormRequest extends FormRequest
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
            'nome' => 'required',
            'categoria_empresa_id' => 'required',
            'nuit' => 'required|integer|digits:9',
            'email' => [
                'nullable',
                'email',
                'email' => 'unique:empresas,email,'.$id],
            'endereco' => 'required|string'
        ];
    }
}
