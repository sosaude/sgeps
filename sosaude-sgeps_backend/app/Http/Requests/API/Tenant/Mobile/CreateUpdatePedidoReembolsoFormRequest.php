<?php

namespace App\Http\Requests\API\Tenant\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdatePedidoReembolsoFormRequest extends FormRequest
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
        return [
            'empresa_id' => 'required|integer',
            'unidade_sanitaria' => 'required|string|max:255',
            'servico_prestado' => 'required|string|max:255',
            'nr_comprovativo' => 'required|string|max:100',
            'valor' => 'required|numeric',
            'data' => 'required|date',
            // 'ficheiros' => 'required|array',
            // 'ficheiros' => 'required|array',
            'ficheiros' => 'nullable|array',
            // 'ficheiros.*' => 'required|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'ficheiros.*' => 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'beneficio_proprio_beneficiario' => 'required|boolean',
            // 'beneficiario_id' => 'nullable|required_if:beneficio_proprio_beneficiario,1|integer|exists:beneficiarios,id', // não precis, pois é encontrado com base no cliente
            'dependente_beneficiario_id' => 'nullable|required_if:beneficio_proprio_beneficiario,0|integer|exists:dependente_beneficiarios,id',
            'comentario' => 'nullable|string|max:255',
            // 'comprovativo' => 'nullable',
        ];
    }
}
