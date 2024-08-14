<?php

namespace App\Http\Requests\API;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateServicoFormRequest extends FormRequest
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
        $servico_id = request()->route('id');
        // dd($servico_id);

        return [
            'nome' => 'required|string|unique:servicos,nome,'.$servico_id,
            'categoria_servico_id' => 'required|integer|exists:categoria_servicos,id'
        ];
    }
}
