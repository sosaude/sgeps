<?php

namespace App\Http\Requests\API\Tenant\Empresa;

use App\Models\PlanoSaude;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreateUpdatePlanoSaudeFormRequest extends FormRequest
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
        $general_rules = [];
        $new_plano_saude_rules = [];
        $grupo_beneficiario_rules = [];
        $empresa_id = request('empresa_id');
        $grupo_beneficiario_id = request('grupo_beneficiario_id');
        $plano_saude_id = null;

        /* if (null !== request('grupo_beneficiario_id')) {
            $grupo_beneficiario_rules = ['grupo_beneficiario_id' => 'integer|exists:grupo_beneficiarios,id'];
        } */
        // dd(['grupos_medicamento_plano.*.id']);
        // O plano deve ser único para cada grupo, cada grupo deve ter seu único plano
        // Cada empresa deve ter apenas um plano default ou seja grupo_beneficiario_id = null,
        // Se o plano já existir, valida-se a unicidade do grupo associado, não pode existir mais de um plano para o mesmo grupo. Aqui é o UPDATE. Será ignorado o plano cujo grupo_beneficiario_id não tenha sido alterado no formulário durante a actualização dos dados, isto para não validar a unicidade deste plano para o grupo em causa. Essa rule irá disparar se for informado o id do plano e um outro grupo_benefiario_id que já esteja em uso(Durante o update)
        // Se o plano não existir ainda, valida-se a unicidade do grupo associado, não pode existir mais de um plano para o mesmo grupo
        if (null !== request('plano_saude_id')) {

            $plano_saude = PlanoSaude::find(request('plano_saude_id'));
            isset($plano_saude) ? $plano_saude_id = $plano_saude->id : $plano_saude_id = $plano_saude_id;

            $new_plano_saude_rules = ['grupo_beneficiario_id' => "unique:plano_saudes,grupo_beneficiario_id,{$plano_saude_id},id,empresa_id,{$empresa_id}"];
        } else {
            // $new_plano_saude_rules = ['grupo_beneficiario_id' => "unique:plano_saudes,grupo_beneficiario_id"];
            /* $new_plano_saude_rules = ['grupo_beneficiario_id' =>  [ // Verifica se já existe um plano padrão para esta Empresa. Se existir retorna erro de validação
                function ($attribute, $value, $fail) use ($grupo_beneficiario_id, $empresa_id) {
                    if (!isset($grupo_beneficiario_id)) {
                        if ($this->validarGrupoBeneficiarioDaEmpresaAoPlano($empresa_id)) {
                            $fail($attribute . ' Já existe um plano padrão para a sua Empresa');
                        }
                    }
                },
            ],] */
            
            $new_plano_saude_rules = ['grupo_beneficiario_id' =>  "unique:plano_saudes,grupo_beneficiario_id,{$plano_saude_id},id,empresa_id,{$empresa_id}",];
        }

        $general_rules = [
            'padrao' => 'required|boolean',
            'empresa_id' => 'required|integer',
            'beneficio_anual_segurando_limitado' => 'required|boolean',
            'valor_limite_anual_segurando' => 'nullable|numeric|required_if:beneficio_anual_segurando_limitado,1',
            'limite_fora_area_cobertura' => 'required|boolean',
            'valor_limite_fora_area_cobertura' => 'nullable|numeric|required_if:limite_fora_area_cobertura,1',
            'regiao_cobertura' => 'required|array',
            // 'regiao_cobertura.*' => 'integer|exists:pais,id',
            'grupos_medicamento_plano' => 'required|array',
            'grupos_medicamento_plano.*.comparticipacao_factura' => 'required|boolean',
            'grupos_medicamento_plano.*.valor_comparticipacao_factura' => 'nullable|numeric|required_if:grupos_medicamento_plano.*.comparticipacao_factura,1',
            'grupos_medicamento_plano.*.beneficio_ilimitado' => 'required|boolean',
            'grupos_medicamento_plano.*.valor_beneficio_limitado' => 'nullable|required_if:grupos_medicamento_plano.*.beneficio_ilimitado,0',
            // 'grupos_medicamento_plano.*.grupo_medicamento_id' => 'required|integer|exists:grupo_medicamentos,id',
            'grupos_medicamento_plano.*.medicamentos' => 'nullable|array',
            // 'grupos_medicamento_plano.*.medicamentos.*.id' => 'required_with:grupos_medicamento_plano.*.medicamentos|integer|exists:medicamentos,id',
            'grupos_medicamento_plano.*.medicamentos.*.coberto' => 'required_with:grupos_medicamento_plano.*.medicamentos|boolean',
            'grupos_medicamento_plano.*.medicamentos.*.pre_autorizacao' => 'required_with:grupos_medicamento_plano.*.medicamentos|boolean',

            'categorias_servico_plano' => 'required|array',
            'categorias_servico_plano.*.comparticipacao_factura' => 'required|boolean',
            'categorias_servico_plano.*.valor_comparticipacao_factura' => 'nullable|numeric|required_if:categorias_servico_plano.*.comparticipacao_factura,1',
            'categorias_servico_plano.*.beneficio_ilimitado' => 'required|boolean',
            'categorias_servico_plano.*.valor_beneficio_limitado' => 'nullable|required_if:categorias_servico_plano.*.beneficio_ilimitado,0',
            // 'categorias_servico_plano.*.categoria_servico_id' => 'required|integer|exists:categoria_servicos,id',
            'categorias_servico_plano.*.servicos' => 'nullable|array',
            // 'categorias_servico_plano.*.servicos.*.id' => 'required_with:categorias_servico_plano.*.servicos|integer|exists:servicos,id',
            'categorias_servico_plano.*.servicos.*.coberto' => 'required_with:categorias_servico_plano.*.servicos|boolean',
            'categorias_servico_plano.*.servicos.*.pre_autorizacao' => 'required_with:categorias_servico_plano.*.servicos|boolean',
        ];

        return array_merge($general_rules, $new_plano_saude_rules, $grupo_beneficiario_rules);
    }



    /*'grupo_beneficiario_id' =>  [ // Verifica se já existe um plano padrão para esta Empresa. Se existir retorna erro de validação
                function ($attribute, $value, $fail) use ($grupo_beneficiario_id, $empresa_id) {
                    if (!isset($grupo_beneficiario_id)) {
                        if ($this->validarGrupoBeneficiarioDaEmpresaAoPlano($empresa_id)) {
                            $fail($attribute . ' Já existe um plano padrão para a sua Empresa');
                        }
                    }
                },
            ], */

    private function validarGrupoBeneficiarioDaEmpresaAoPlano($empresa_id)
    {
        
        $plano_saude = PlanoSaude::byEmpresa($empresa_id)
            ->where('grupo_beneficiario_id', null)
            // ->orWhere('grupo_beneficiario_id', null)
            ->first();
            
        if (!empty($plano_saude)) {
            return true;
        }

        return false;
    }
}
