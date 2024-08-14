<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CreateUpdateMedicamentoFormRequest extends FormRequest
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
        $sub_classe_medicamento_id = Request::get('sub_classe_medicamento_id');
        $sub_grupo_medicamento_id = Request::get('sub_grupo_medicamento_id');
        $grupo_medicamento_id = Request::get('grupo_medicamento_id');
        $nome_generico_medicamento_id = Request::get('nome_generico_medicamento_id');
        $forma_medicamento_id = Request::get('forma_medicamento_id');
        $dosagem = Request::get('dosagem');
        // dd($sub_classe_medicamento_id);
        return [
            'codigo' => 'required',
            'dosagem' => 'required',
            'forma_medicamento_id' => 'required|integer|exists:forma_medicamentos,id',
            'grupo_medicamento_id' => 'required|integer|exists:grupo_medicamentos,id',
            'sub_grupo_medicamento_id' => 'required|integer|exists:sub_grupo_medicamentos,id',
            'sub_classe_medicamento_id' => 'nullable|integer|exists:sub_classe_medicamentos,id',
            'nome_generico_medicamento_id' => [
                'required',
                'integer',
                'exists:nome_generico_medicamentos,id',
                'unique:medicamentos,nome_generico_medicamento_id,'.$this->id.',id,sub_grupo_medicamento_id,'.$sub_grupo_medicamento_id.',grupo_medicamento_id,'.$grupo_medicamento_id.',forma_medicamento_id,'.$forma_medicamento_id.',nome_generico_medicamento_id,'.$nome_generico_medicamento_id.',dosagem,'.$dosagem
            ],
            
        ];
    }
}
