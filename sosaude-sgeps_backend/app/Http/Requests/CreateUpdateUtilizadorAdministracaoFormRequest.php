<?php

namespace App\Http\Requests;

use App\Models\UtilizadorAdministracao;
use Illuminate\Foundation\Http\FormRequest;

class CreateUpdateUtilizadorAdministracaoFormRequest extends FormRequest
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
        $utilizador_administracao_id = $this->route('id');
        $utilizado_administracao = UtilizadorAdministracao::with('user')->find($utilizador_administracao_id);
        $user_id = null;

        if (!empty($utilizado_administracao)) {
            if (!empty($utilizado_administracao->user)) {
                $user_id = $utilizado_administracao->user_id;
            }
        }

        return [
            'nome' => 'required|string|max:100',
            'email' => 'required|email',
            'activo' => 'required|boolean',
            'role_id' => 'required|integer|exists:roles,id',
            'email' => "unique:utilizador_administracaos,email,$utilizador_administracao_id",
            'email' => "unique:users,email,$user_id"
        ];

    }
}
