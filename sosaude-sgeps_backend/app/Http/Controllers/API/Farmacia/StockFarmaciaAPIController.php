<?php

namespace App\Http\Controllers\API\Farmacia;

use App\Models\User;
use App\Models\PlanoSaude;
use App\Models\Medicamento;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use App\Models\StockFarmacia;
use App\Models\FormaMedicamento;
use App\Models\MarcaMedicamento;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\AppBaseController;

class StockFarmaciaAPIController extends AppBaseController
{
    //
    private $stock_farmacia;
    private $medicamento;
    private $marca_medicamento;
    private $plano_saude;

    public function __construct(
        StockFarmacia $stock_farmacia,
        Medicamento $medicamento,
        MarcaMedicamento $marca_medicamento,
        FormaMedicamento $forma_medicamento,
        PlanoSaude $plano_saude
    ) {
        $this->stock_farmacia = $stock_farmacia;
        $this->medicamento = $medicamento;
        $this->marca_medicamento = $marca_medicamento;
        $this->plano_saude = $plano_saude;
    }

    public function getMarcasMedicamentosAdministracao()
    {
        if (Gate::denies('gerir stock')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $marcas_medicamentos = $this->marca_medicamento
            ->with([
                'medicamento',
                'medicamento.nomeGenerico:id,nome',
                'medicamento.formaMedicamento:id,forma',
            ])
            ->get()
            ->map(function ($marca_medicamento) {
                // return $marca_medicamento;

                return [
                    'marca_medicamento_id' => $marca_medicamento->id,
                    'marca' => $marca_medicamento->marca,
                    'marca_codigo' => $marca_medicamento->codigo,
                    'marca_pais_origem' => $marca_medicamento->pais_origem,
                    'medicamento_id' => empty($marca_medicamento->medicamento) ? '' : $marca_medicamento->medicamento->id,
                    'medicamento_codigo' => empty($marca_medicamento->medicamento) ? '' : $marca_medicamento->medicamento->codigo,
                    'medicamento_dosagem' => empty($marca_medicamento->medicamento) ? '' : $marca_medicamento->medicamento->dosagem,
                    'medicamento_nome_generico_id' => empty($marca_medicamento->medicamento->nomeGenerico) ? '' : $marca_medicamento->medicamento->nomeGenerico->id,
                    'medicamento_nome_generico' => empty($marca_medicamento->medicamento->nomeGenerico) ? '' : $marca_medicamento->medicamento->nomeGenerico->nome,
                    'medicamento_forma_id' => empty($marca_medicamento->medicamento->formaMedicamento) ? '' : $marca_medicamento->medicamento->formaMedicamento->id,
                    'medicamento_forma' => empty($marca_medicamento->medicamento->formaMedicamento) ? '' : $marca_medicamento->medicamento->formaMedicamento->forma,
                ];
            });

        // dd($marcas_medicamentos);

        $data = [
            'marcas_medicamentos' => $marcas_medicamentos,
        ];

        return $this->sendResponse($data, '', 200);
    }


    public function getStock()
    {
        if (Gate::denies('gerir stock')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $farmacia_id = request('farmacia_id');

        /* $stocks_farmacia = $this->stock_farmacia
        ->byFarmacia($farmacia_id)
        ->with([
            'marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
            'medicamento',
            'medicamento.nomeGenerico:id,nome',
            'medicamento.formaMedicamento:id,forma',
            ])
        ->get()
        ->map( function ($stock_farmacia) {
            // return $marca_medicamento;

            return [
                'id' => $stock_farmacia->id,
                'preco' => $stock_farmacia->preco,
                'quantidade_disponivel' => $stock_farmacia->quantidade_disponivel,
                'marca_medicamento_id' => $stock_farmacia->marca_medicamento_id,
                'marca' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->marca,
                'marca_codigo' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->codigo,
                'marca_pais_origem' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->pais_origem,
                'medicamento_id' => empty($stock_farmacia->medicamento) ? '' : $stock_farmacia->medicamento->id,
                'medicamento_codigo' => empty($stock_farmacia->medicamento) ? '' : $stock_farmacia->medicamento->codigo,
                'medicamento_dosagem' => empty($stock_farmacia->medicamento) ? '' : $stock_farmacia->medicamento->dosagem,
                'medicamento_nome_generico_id' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->id,
                'medicamento_nome_generico' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->nome,
                'medicamento_forma_id' => empty($stock_farmacia->medicamento->formaMedicamento) ? '' : $stock_farmacia->medicamento->formaMedicamento->id,
                'medicamento_forma' => empty($stock_farmacia->medicamento->formaMedicamento) ? '' : $stock_farmacia->medicamento->formaMedicamento->forma,
            ];
        }); */

        $stocks_farmacia = $this->stock_farmacia
            ->byFarmacia($farmacia_id)
            ->with([
                'marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                'marcaMedicamento.medicamento',
                'marcaMedicamento.medicamento.nomeGenerico:id,nome',
                'marcaMedicamento.medicamento.formaMedicamento:id,forma',
            ])
            ->get()
            ->map(function ($stock_farmacia) {
                // return $marca_medicamento;

                return [
                    'id' => $stock_farmacia->id,
                    'preco' => $stock_farmacia->preco,
                    'iva' => $stock_farmacia->iva,
                    'preco_iva' => $stock_farmacia->preco_iva,
                    'quantidade_disponivel' => $stock_farmacia->quantidade_disponivel,
                    'marca_medicamento_id' => $stock_farmacia->marca_medicamento_id,
                    'marca' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->marca,
                    'marca_codigo' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->codigo,
                    'marca_pais_origem' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->pais_origem,
                    'medicamento_id' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->id,
                    'medicamento_codigo' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->codigo,
                    'medicamento_dosagem' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->dosagem,
                    'medicamento_nome_generico_id' => empty($stock_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? '' : $stock_farmacia->marcaMedicamento->medicamento->nomeGenerico->id,
                    'medicamento_nome_generico' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->nome,
                    'medicamento_forma_id' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->id,
                    'medicamento_forma' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma,
                ];
            });

        // dd($marcas_medicamentos);

        $data = [
            'marcas_medicamentos' => $stocks_farmacia,
        ];

        return $this->sendResponse($data, '', 200);
    }






    public function getStockIniciarVenda($beneficiario_id)
    {
        if (Gate::denies('gerir stock')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $request->validate(['beneficiario_id' => 'required|integer']);
        $farmacia_id = request('farmacia_id');
        // $beneficiario_id = $request->beneficiario_id;
        $grupos_medicamento_plano = [];
        $medicamento_pre_autorizacao_ids = [];
        $medicamento_coberto_ids = [];

        $beneficiao = Beneficiario::where('id', $beneficiario_id)->first();
        if (empty($beneficiao))
            return $this->sendError('Beneficiário não encontrado!', 404);

        $plano_saude = $this->plano_saude->where('grupo_beneficiario_id', $beneficiao->grupo_beneficiario_id)
            ->with(
                'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id', // if leave id and not medicamento it is ambiguous
                'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome'
            )
            ->first();

        if (empty($plano_saude))
            return $this->sendError('Plano de Saúde do Beneficiário não encontrado!', 404);

        if (!empty($plano_saude->gruposMedicamentoPlano)) {
            $grupos_medicamento_plano = $plano_saude->gruposMedicamentoPlano->map(function ($grupo_medicamento_plano) {
                $medicamentos = null;

                if (!empty($grupo_medicamento_plano->medicamentos)) {
                    $medicamentos = $grupo_medicamento_plano->medicamentos
                        ->map(function ($medicamento) {
                            return [
                                'id' => $medicamento->id, // if leave id and not medicamento it is ambiguous
                                'nome_generico' => empty($medicamento->nomeGenerico) ? '' : $medicamento->nomeGenerico->nome,
                                'coberto' => $medicamento->pivot->coberto,
                                'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                            ];
                        });
                }
                return [
                    'medicamentos' => $medicamentos,
                ];
            });
        }

        // Pegar os ids dos medicamento que precisam da pre-autorizacao a partir dos grupos_medicamento_plano do plano encontrado acima
        if ($grupos_medicamento_plano) {
            foreach ($grupos_medicamento_plano->toArray() as $key => $grupo_medicamento_plano) {
                foreach ($grupo_medicamento_plano['medicamentos'] as $key => $medicamento) {
                    if ($medicamento['coberto'] == true)
                        array_push($medicamento_coberto_ids, $medicamento['id']);

                    if ($medicamento['pre_autorizacao'] == true)
                        array_push($medicamento_pre_autorizacao_ids, $medicamento['id']);
                }
            }
        }
        // dd($medicamento_pre_autorizacao_ids);
        $stocks_farmacia = $this->stock_farmacia
            ->byFarmacia($farmacia_id)
            ->with([
                'marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                'marcaMedicamento.medicamento',
                'marcaMedicamento.medicamento.nomeGenerico:id,nome',
                'marcaMedicamento.medicamento.formaMedicamento:id,forma',
            ])
            ->get()
            ->map(function ($stock_farmacia) use ($medicamento_coberto_ids, $medicamento_pre_autorizacao_ids) {

                return [
                    'id' => $stock_farmacia->id,
                    'preco' => $stock_farmacia->preco,
                    'iva' => $stock_farmacia->iva,
                    'preco_iva' => $stock_farmacia->preco_iva,
                    'quantidade_disponivel' => $stock_farmacia->quantidade_disponivel,
                    'coberto' => in_array($stock_farmacia->medicamento_id, $medicamento_coberto_ids) ? true : false,
                    'pre_autorizacao' => in_array($stock_farmacia->medicamento_id, $medicamento_pre_autorizacao_ids) ? true : false,
                    'marca_medicamento_id' => $stock_farmacia->marca_medicamento_id,
                    'marca' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->marca,
                    'marca_codigo' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->codigo,
                    'marca_pais_origem' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->pais_origem,
                    'medicamento_id' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->id,
                    'medicamento_codigo' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->codigo,
                    'medicamento_dosagem' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->dosagem,
                    'medicamento_nome_generico_id' => empty($stock_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? '' : $stock_farmacia->marcaMedicamento->medicamento->nomeGenerico->id,
                    'medicamento_nome_generico' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->nome,
                    'medicamento_forma_id' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->id,
                    'medicamento_forma' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma,
                ];
            });

        // dd($plano_saude);

        // dd($medicamento_pre_autorizacao_ids);
        $data = [
            'marcas_medicamentos' => $stocks_farmacia,
        ];

        return $this->sendResponse($data, '', 200);
    }


    public function getStockIniciarPedidoAprovacao($beneficiario_id)
    {
        if (Gate::denies('gerir stock')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $request->validate(['beneficiario_id' => 'required|integer']);
        $farmacia_id = request('farmacia_id');
        // $beneficiario_id = $request->beneficiario_id;
        $grupos_medicamento_plano = [];
        $medicamento_coberto_ids = [];
        $medicamento_pre_autorizacao_ids = [];

        $beneficiario = Beneficiario::where('id', $beneficiario_id)->first();
        if (empty($beneficiario))
            return $this->sendError('Beneficiário não encontrado!', 404);

        $plano_saude = $this->plano_saude->where('grupo_beneficiario_id', $beneficiario->grupo_beneficiario_id)
            ->with(
                'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id', // if leave id and not medicamento it is ambiguous
                'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome',

                'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                'categoriasServicoPlano.categoriaServico:id,nome',
                'categoriasServicoPlano.servicos:id,nome' // if leave id instead of servico_id it is ambiguous
            )
            ->first();

        if (empty($plano_saude))
            return $this->sendError('Plano de Saúde do Beneficiário não encontrado!', 404);

        if (!empty($plano_saude->gruposMedicamentoPlano)) {
            $grupos_medicamento_plano = $plano_saude->gruposMedicamentoPlano->map(function ($grupo_medicamento_plano) {
                $medicamentos = null;

                if (!empty($grupo_medicamento_plano->medicamentos)) {
                    $medicamentos = $grupo_medicamento_plano->medicamentos
                        ->filter(function ($medicamento, $key) {
                            return $medicamento->pivot->pre_autorizacao == true;
                        })
                        ->values()
                        ->map(function ($medicamento) {
                            return [
                                'id' => $medicamento->id, // if leave id and not medicamento it is ambiguous
                                'nome_generico' => empty($medicamento->nomeGenerico) ? '' : $medicamento->nomeGenerico->nome,
                                'coberto' => $medicamento->pivot->coberto,
                                'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                            ];
                        });
                }
                return [
                    'medicamentos' => $medicamentos,
                ];
            });
        }
        // Pegar os ids dos medicamento que precisam da pre-autorizacao a partir dos grupos_medicamento_plano do plano encontrado acima
        if ($grupos_medicamento_plano) {
            foreach ($grupos_medicamento_plano->toArray() as $key => $grupo_medicamento_plano) {
                foreach ($grupo_medicamento_plano['medicamentos'] as $key => $medicamento) {
                    // array_push($medicamento_pre_autorizacao_ids, $medicamento['id']);

                    if ($medicamento['coberto'] == true)
                        array_push($medicamento_coberto_ids, $medicamento['id']);

                    if ($medicamento['pre_autorizacao'] == true)
                        array_push($medicamento_pre_autorizacao_ids, $medicamento['id']);
                }
            }
        }

        $stocks_farmacia = $this->stock_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('medicamento_id', $medicamento_pre_autorizacao_ids)
            ->with([
                'marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                'marcaMedicamento.medicamento',
                'marcaMedicamento.medicamento.nomeGenerico:id,nome',
                'marcaMedicamento.medicamento.formaMedicamento:id,forma',
            ])
            ->get()
            ->map(function ($stock_farmacia) use ($medicamento_coberto_ids, $medicamento_pre_autorizacao_ids) {
                // return $marca_medicamento;

                return [
                    'id' => $stock_farmacia->id,
                    'preco' => $stock_farmacia->preco,
                    'iva' => $stock_farmacia->iva,
                    'preco_iva' => $stock_farmacia->preco_iva,
                    'quantidade_disponivel' => $stock_farmacia->quantidade_disponivel,
                    'coberto' => in_array($stock_farmacia->medicamento_id, $medicamento_coberto_ids) ? true : false,
                    'pre_autorizacao' => in_array($stock_farmacia->medicamento_id, $medicamento_pre_autorizacao_ids) ? true : false,
                    'marca_medicamento_id' => $stock_farmacia->marca_medicamento_id,
                    'marca' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->marca,
                    'marca_codigo' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->codigo,
                    'marca_pais_origem' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->pais_origem,
                    'medicamento_id' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->id,
                    'medicamento_codigo' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->codigo,
                    'medicamento_dosagem' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->dosagem,
                    'medicamento_nome_generico_id' => empty($stock_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? '' : $stock_farmacia->marcaMedicamento->medicamento->nomeGenerico->id,
                    'medicamento_nome_generico' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->nome,
                    'medicamento_forma_id' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->id,
                    'medicamento_forma' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma,
                ];
            });

        // dd($plano_saude);

        // dd($medicamento_pre_autorizacao_ids);
        $data = [
            'marcas_medicamentos' => $stocks_farmacia,
        ];

        return $this->sendResponse($data, '', 200);
    }

    public function setMarcasMedicamentosFarmacia(Request $request)
    {
        if (Gate::denies('gerir stock')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $farmacia_id = $request->farmacia_id;
        $medicamento_id = $request->medicamento_id;
        $request->validate([
            'medicamento_id' => 'required|integer',
            'marca_medicamento_id' => "required|integer|unique:stock_farmacias,marca_medicamento_id,NULL,id,farmacia_id,$farmacia_id",
            'preco' => 'required|numeric',
            'iva' => 'required|numeric',
            'preco_iva' => 'required|numeric',
            'quantidade_disponivel' => 'required|integer',
            'farmacia_id' => 'required|integer',
        ]);

        $input = $request->only(['medicamento_id', 'marca_medicamento_id', 'preco', 'iva', 'preco_iva', 'quantidade_disponivel', 'farmacia_id']);
        // dd('passou');
        DB::beginTransaction();
        try {
            $stock_farmacia = $this->stock_farmacia->create($input);
            DB::commit();

            $data = [
                'id' => $stock_farmacia->id,
                'preco' => $stock_farmacia->preco,
                'iva' => $stock_farmacia->iva,
                'preco_iva' => $stock_farmacia->preco_iva,
                'quantidade_disponivel' => $stock_farmacia->quantidade_disponivel,
                'marca_medicamento_id' => $stock_farmacia->marca_medicamento_id,
                'marca' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->marca,
                'marca_codigo' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->codigo,
                'marca_pais_origem' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->pais_origem,
                'medicamento_id' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->id,
                'medicamento_codigo' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->codigo,
                'medicamento_dosagem' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->dosagem,
                'medicamento_nome_generico_id' => empty($stock_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? '' : $stock_farmacia->marcaMedicamento->medicamento->nomeGenerico->id,
                'medicamento_nome_generico' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->nome,
                'medicamento_forma_id' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->id,
                'medicamento_forma' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma,
            ];
            return $this->sendResponse($data, '', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }


    public function actualizarStock(Request $request)
    {


        if (Gate::denies('gerir stock')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }


        // NOTA: deve se disconsiderar o medicamento_id quando s trata do stock, na verdade este atributo devia deixar de existir nesta tabela.

        $request->validate([
            'id' => 'required|integer',
            'farmacia_id' => 'required|integer',
            'medicamento_id' => 'required|integer',
            // 'marca_medicamento_id' => "required|integer|unique:stock_farmacias,marca_medicamento_id,$request->id,id,medicamento_id,$request->medicamento_id,farmacia_id,$request->farmacia_id",
            'marca_medicamento_id' => "required|integer|unique:stock_farmacias,marca_medicamento_id,$request->id,id,farmacia_id,$request->farmacia_id",
            'preco' => 'required|numeric',
            'iva' => 'required|numeric',
            'preco_iva' => 'required|numeric',
            'quantidade_disponivel' => 'required|integer',

        ]);

        // $farmacia_id = $request->farmacia_id;
        // $medicamento_id = $request->medicamento_id;
        $stock_farmacia = $this->stock_farmacia
            ->where('id', $request->id)
            ->where('farmacia_id', $request->farmacia_id)
            ->first();
        if (empty($stock_farmacia))
            return $this->sendError('Item não encontrado!', 404);

        // dd($stock_farmacia);


        $input = $request->only(['medicamento_id', 'marca_medicamento_id', 'preco', 'iva', 'preco_iva', 'quantidade_disponivel', 'farmacia_id']);
        // dd('passou');
        DB::beginTransaction();
        try {
            $stock_farmacia->update($input);
            DB::commit();

            $data = [
                'id' => $stock_farmacia->id,
                'preco' => $stock_farmacia->preco,
                'iva' => $stock_farmacia->iva,
                'preco_iva' => $stock_farmacia->preco_iva,
                'quantidade_disponivel' => $stock_farmacia->quantidade_disponivel,
                'marca_medicamento_id' => $stock_farmacia->marca_medicamento_id,
                'marca' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->marca,
                'marca_codigo' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->codigo,
                'marca_pais_origem' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->pais_origem,
                'medicamento_id' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->id,
                'medicamento_codigo' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->codigo,
                'medicamento_dosagem' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->dosagem,
                'medicamento_nome_generico_id' => empty($stock_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? '' : $stock_farmacia->marcaMedicamento->medicamento->nomeGenerico->id,
                'medicamento_nome_generico' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->nome,
                'medicamento_forma_id' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->id,
                'medicamento_forma' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma,
            ];
            return $this->sendResponse($data, '', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    public function removeMarcasMedicamentoFarmacia($id)
    {
        $farmacia_id = request('farmacia_id');

        $stock_farmacia = $this->stock_farmacia
            ->where('id', $id)
            ->where('farmacia_id', $farmacia_id)
            ->first();

        if (empty($stock_farmacia))
            return $this->sendError('Item não encontrado!', 404);

        DB::beginTransaction();
        try {

            $stock_farmacia->delete();
            DB::commit();   
            
            return $this->sendSuccess('Removido com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
