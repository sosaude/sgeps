<?php

namespace App\Http\Requests\API;

use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateMarcaMedicamentoFormRequest extends FormRequest
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
        return [
            'medicamento_id' => 'required',
            'codigo' => 'required|string',
            'marca' => [
                'required',
                'string',
                'unique:marca_medicamentos,marca,' . $this->id . ',id,medicamento_id,' . $medicamento_id
            ],
        ];
    }
}
