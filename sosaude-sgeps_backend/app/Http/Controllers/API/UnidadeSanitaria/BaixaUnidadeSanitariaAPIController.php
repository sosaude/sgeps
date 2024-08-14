<?php

namespace App\Http\Controllers\API\UnidadeSanitaria;

use App\Models\User;
use App\Models\Servico;
use App\Models\EstadoBaixa;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\BaixaUnidadeSanitaria;
use App\Models\ItenBaixaUnidadeSanitaria;
use App\Http\Controllers\AppBaseController;
use App\Helpers\Unidade_Sanitaria\RulesManager;
use App\Models\UnidadeSanitaria;

class BaixaUnidadeSanitariaAPIController extends AppBaseController
{
    // private $stock_farmacia;
    private $unidade_sanitaria;
    private $baixa_unidade_sanitaria;
    private $servico;
    private $estado_baixa;

    public function __construct(
        /* StockFarmacia $stock_farmacia, */
        UnidadeSanitaria $unidade_sanitaria,
        BaixaUnidadeSanitaria $baixa_unidade_sanitaria,
        Servico $servico,
        EstadoBaixa $estado_baixa
    ) {
        // $this->stock_farmacia = $stock_farmacia;
        $this->unidade_sanitaria = $unidade_sanitaria;
        $this->baixa_unidade_sanitaria = $baixa_unidade_sanitaria;
        $this->servico = $servico;
        $this->estado_baixa = $estado_baixa;
    }

    public function getBaixasUnidadeSanitaria()
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $estados_baixas_gasto = $this->estado_baixa
            ->whereIn('codigo', [10, 11, 12, 13])
            ->pluck('id')
            ->toArray();
        $baixas_unidade_sanitaria = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto);

        $data = [
            'baixas' => $baixas_unidade_sanitaria,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }
    public function getBaixasUnidadeSanitariaExcel()
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $estados_baixas_gasto = $this->estado_baixa
            ->whereIn('codigo', [10, 11, 12, 13])
            ->pluck('id')
            ->toArray();
        $baixas_unidade_sanitaria = $this->getBaixasUnidadeSanitariaByEstadoExcel($estados_baixas_gasto);

        $data = [
            'baixas' => $baixas_unidade_sanitaria,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }


    public function getBaixasUnidadeSanitariaByEstado(array $estados_baixas)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        $baixas_unidade_sanitaria = [];

        if (!empty($estados_baixas)) {

            $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
                ->byUnidadeSanitaria($unidade_sanitaria_id)
                ->whereIn('estado_baixa_id', $estados_baixas)
                ->with([
                    'beneficiario:id,nome',
                    'dependenteBeneficiario:id,nome',
                    'unidadeSanitaria:id,nome',
                    'estadoBaixa:id,nome,codigo',
                    'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,baixa_unidade_sanitaria_id,servico_id',
                    'itensBaixaUnidadeSanitaria.servico:id,nome'
                ])
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->map(function ($baixa_unidade_sanitaria) {
                    $descricao = [];
                    if ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria) {
                        foreach ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria as $iten_baixa_unidade_sanitaria) {
                            // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                            $descricao_actual = [
                                'id' => $iten_baixa_unidade_sanitaria->id,
                                'servico_id' => $iten_baixa_unidade_sanitaria->servico_id,
                                'servico_nome' => !empty($iten_baixa_unidade_sanitaria->servico) ? $iten_baixa_unidade_sanitaria->servico->nome : '',
                                'quantidade' => $iten_baixa_unidade_sanitaria->quantidade,
                                'preco' => $iten_baixa_unidade_sanitaria->preco,
                                'iva' => $iten_baixa_unidade_sanitaria->iva,
                                'preco_iva' => $iten_baixa_unidade_sanitaria->preco_iva,
                            ];
                            array_push($descricao, $descricao_actual);
                            $descricao_actual = [];
                        }
                    }

                    $responsavel = $baixa_unidade_sanitaria->responsavel;
                    $comentario_baixa = $baixa_unidade_sanitaria->comentario_baixa;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    return [
                        'id' => $baixa_unidade_sanitaria->id,
                        'proveniencia' => $baixa_unidade_sanitaria->proveniencia,
                        'empresa_id' => $baixa_unidade_sanitaria->empresa_id,
                        'beneficio_proprio_beneficiario' => $baixa_unidade_sanitaria->beneficio_proprio_beneficiario,
                        'beneficiario_id' => $baixa_unidade_sanitaria->beneficiario_id,
                        'dependente_beneficiario_id' => $baixa_unidade_sanitaria->dependente_beneficiario_id,
                        'nome_beneficiario' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : '',
                        'nome_dependente' => !empty($baixa_unidade_sanitaria->dependenteBeneficiario) ? $baixa_unidade_sanitaria->dependenteBeneficiario->nome : '',
                        'nome_instituicao' => !empty($baixa_unidade_sanitaria->unidadeSanitaria) ? $baixa_unidade_sanitaria->unidadeSanitaria->nome : '',
                        'valor_baixa' => $baixa_unidade_sanitaria->valor,
                        'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                        'nr_comprovativo' => $baixa_unidade_sanitaria->nr_comprovativo,
                        'responsavel' => $responsavel,
                        // 'estado' => $baixa_farmacia->estado,
                        'estado_id' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->id : '',
                        'estado_codigo' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->codigo : '',
                        'estado_nome' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->nome : '',
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'data_baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                        'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                        'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                        'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                        'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_unidade_sanitaria->comentario_pedido_aprovacao,
                        'comentario_baixa' => $comentario_baixa,
                        'descricao' => $descricao,
                    ];
                });
        } else {

            $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
                ->byUnidadeSanitaria($unidade_sanitaria_id)
                ->with([
                    'beneficiario:id,nome',
                    'dependenteBeneficiario:id,nome',
                    'unidadeSanitaria:id,nome',
                    'estadoBaixa:id,nome,codigo',
                    'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,baixa_unidade_sanitaria_id,servico_id',
                    'itensBaixaUnidadeSanitaria.servico:id,nome'
                ])
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->map(function ($baixa_unidade_sanitaria) {
                    $descricao = [];
                    if ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria) {
                        foreach ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria as $iten_baixa_unidade_sanitaria) {
                            // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                            $descricao_actual = [
                                'id' => $iten_baixa_unidade_sanitaria->id,
                                'servico_id' => $iten_baixa_unidade_sanitaria->servico_id,
                                'servico_nome' => !empty($iten_baixa_unidade_sanitaria->servico) ? $iten_baixa_unidade_sanitaria->servico->nome : '',
                                'quantidade' => $iten_baixa_unidade_sanitaria->quantidade,
                                'preco' => $iten_baixa_unidade_sanitaria->preco,
                                'iva' => $iten_baixa_unidade_sanitaria->iva,
                                'preco_iva' => $iten_baixa_unidade_sanitaria->preco_iva,
                            ];
                            array_push($descricao, $descricao_actual);
                            $descricao_actual = [];
                        }
                    }

                    $responsavel = $baixa_unidade_sanitaria->responsavel;
                    $comentario_baixa = $baixa_unidade_sanitaria->comentario_baixa;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    return [
                        'id' => $baixa_unidade_sanitaria->id,
                        'proveniencia' => $baixa_unidade_sanitaria->proveniencia,
                        'empresa_id' => $baixa_unidade_sanitaria->empresa_id,
                        'beneficio_proprio_beneficiario' => $baixa_unidade_sanitaria->beneficio_proprio_beneficiario,
                        'beneficiario_id' => $baixa_unidade_sanitaria->beneficiario_id,
                        'dependente_beneficiario_id' => $baixa_unidade_sanitaria->dependente_beneficiario_id,
                        'nome_beneficiario' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : '',
                        'nome_dependente' => !empty($baixa_unidade_sanitaria->dependenteBeneficiario) ? $baixa_unidade_sanitaria->dependenteBeneficiario->nome : '',
                        'nome_instituicao' => !empty($baixa_unidade_sanitaria->unidadeSanitaria) ? $baixa_unidade_sanitaria->unidadeSanitaria->nome : '',
                        'valor_baixa' => $baixa_unidade_sanitaria->valor,
                        'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                        'responsavel' => $responsavel,
                        // 'estado' => $baixa_farmacia->estado,
                        'estado_id' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->id : '',
                        'estado_codigo' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->codigo : '',
                        'estado_nome' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->nome : '',
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'data_baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                        'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                        'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                        'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                        'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_unidade_sanitaria->comentario_pedido_aprovacao,
                        'comentario_baixa' => $comentario_baixa,
                        'descricao' => $descricao,
                    ];
                });
        }


        // $data = [
        //     'baixas' => $baixas_unidade_sanitaria,
        // ];

        // return $this->sendResponse($data, 'Baixas Farmacia!', 200);
        return $baixas_unidade_sanitaria;
    }

    public function getBaixasUnidadeSanitariaByEstadoExcel(array $estados_baixas)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        $baixas_unidade_sanitaria = [];

        if (!empty($estados_baixas)) {

            $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
                ->byUnidadeSanitaria($unidade_sanitaria_id)
                ->whereIn('estado_baixa_id', $estados_baixas)
                ->with([
                    'empresa:id,nome',
                    'beneficiario:id,nome',
                    'dependenteBeneficiario:id,nome',
                    'unidadeSanitaria:id,nome',
                    'estadoBaixa:id,nome,codigo',
                    'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,baixa_unidade_sanitaria_id,servico_id',
                    'itensBaixaUnidadeSanitaria.servico:id,nome'
                ])
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->map(function ($baixa_unidade_sanitaria) {
                    $descricao = [];
                    if ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria) {
                        foreach ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria as $iten_baixa_unidade_sanitaria) {
                            // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                            $descricao_actual = [
                                'id' => $iten_baixa_unidade_sanitaria->id,
                                'servico_id' => $iten_baixa_unidade_sanitaria->servico_id,
                                'servico_nome' => !empty($iten_baixa_unidade_sanitaria->servico) ? $iten_baixa_unidade_sanitaria->servico->nome : '',
                                'quantidade' => $iten_baixa_unidade_sanitaria->quantidade,
                                'preco' => $iten_baixa_unidade_sanitaria->preco,
                                'iva' => $iten_baixa_unidade_sanitaria->iva,
                                'preco_iva' => $iten_baixa_unidade_sanitaria->preco_iva,
                            ];
                            array_push($descricao, $descricao_actual);
                            $descricao_actual = [];
                        }
                    }

                    $responsavel = $baixa_unidade_sanitaria->responsavel;
                    $comentario_baixa = $baixa_unidade_sanitaria->comentario_baixa;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    return [
                        'Empresa' =>$baixa_unidade_sanitaria->empresa->nome,
                        // 'id' => $baixa_unidade_sanitaria->id,
                        // 'proveniencia' => $baixa_unidade_sanitaria->proveniencia,
                        // 'empresa_id' => $baixa_unidade_sanitaria->empresa_id,
                        // 'beneficio_proprio_beneficiario' => $baixa_unidade_sanitaria->beneficio_proprio_beneficiario,
                        // 'beneficiario_id' => $baixa_unidade_sanitaria->beneficiario_id,
                        // 'dependente_beneficiario_id' => $baixa_unidade_sanitaria->dependente_beneficiario_id,
                        'Nome Beneficiario' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : '',
                        'Nome Dependente' => !empty($baixa_unidade_sanitaria->dependenteBeneficiario) ? $baixa_unidade_sanitaria->dependenteBeneficiario->nome : '',
                        'Nome instituição' => !empty($baixa_unidade_sanitaria->unidadeSanitaria) ? $baixa_unidade_sanitaria->unidadeSanitaria->nome : '',
                        'Valor' => $baixa_unidade_sanitaria->valor,
                        // 'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                        'Nº comprovativo' => $baixa_unidade_sanitaria->nr_comprovativo,
                        // 'responsavel' => $responsavel,
                        // 'estado' => $baixa_farmacia->estado,
                        // 'estado_id' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->id : '',
                        // 'estado_codigo' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->codigo : '',
                        'Estado' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->nome : '',
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'Data baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                        // 'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                        // 'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                        // 'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                        // 'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_unidade_sanitaria->comentario_pedido_aprovacao,
                        // 'comentario_baixa' => $comentario_baixa,
                        // 'descricao' => $descricao,
                    ];
                });
        } else {

            $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
                ->byUnidadeSanitaria($unidade_sanitaria_id)
                ->with([
                    'beneficiario:id,nome',
                    'dependenteBeneficiario:id,nome',
                    'unidadeSanitaria:id,nome',
                    'estadoBaixa:id,nome,codigo',
                    'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,baixa_unidade_sanitaria_id,servico_id',
                    'itensBaixaUnidadeSanitaria.servico:id,nome'
                ])
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->map(function ($baixa_unidade_sanitaria) {
                    $descricao = [];
                    if ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria) {
                        foreach ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria as $iten_baixa_unidade_sanitaria) {
                            // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                            $descricao_actual = [
                                'id' => $iten_baixa_unidade_sanitaria->id,
                                'servico_id' => $iten_baixa_unidade_sanitaria->servico_id,
                                'servico_nome' => !empty($iten_baixa_unidade_sanitaria->servico) ? $iten_baixa_unidade_sanitaria->servico->nome : '',
                                'quantidade' => $iten_baixa_unidade_sanitaria->quantidade,
                                'preco' => $iten_baixa_unidade_sanitaria->preco,
                                'iva' => $iten_baixa_unidade_sanitaria->iva,
                                'preco_iva' => $iten_baixa_unidade_sanitaria->preco_iva,
                            ];
                            array_push($descricao, $descricao_actual);
                            $descricao_actual = [];
                        }
                    }

                    $responsavel = $baixa_unidade_sanitaria->responsavel;
                    $comentario_baixa = $baixa_unidade_sanitaria->comentario_baixa;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    return [
                        'Empresa' =>$baixa_unidade_sanitaria->empresa->nome,
                        // 'id' => $baixa_unidade_sanitaria->id,
                        // 'proveniencia' => $baixa_unidade_sanitaria->proveniencia,
                        // 'empresa_id' => $baixa_unidade_sanitaria->empresa_id,
                        // 'beneficio_proprio_beneficiario' => $baixa_unidade_sanitaria->beneficio_proprio_beneficiario,
                        // 'beneficiario_id' => $baixa_unidade_sanitaria->beneficiario_id,
                        // 'dependente_beneficiario_id' => $baixa_unidade_sanitaria->dependente_beneficiario_id,
                        'Nome Beneficiario' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : '',
                        'Nome Dependente' => !empty($baixa_unidade_sanitaria->dependenteBeneficiario) ? $baixa_unidade_sanitaria->dependenteBeneficiario->nome : '',
                        'Nome instituição' => !empty($baixa_unidade_sanitaria->unidadeSanitaria) ? $baixa_unidade_sanitaria->unidadeSanitaria->nome : '',
                        'Valor' => $baixa_unidade_sanitaria->valor,
                        // 'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                        'Nº comprovativo' => $baixa_unidade_sanitaria->nr_comprovativo,
                        // 'responsavel' => $responsavel,
                        // 'estado' => $baixa_farmacia->estado,
                        // 'estado_id' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->id : '',
                        // 'estado_codigo' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->codigo : '',
                        'Estado' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->nome : '',
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'Data baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                        // 'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                        // 'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                        // 'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                        // 'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_unidade_sanitaria->comentario_pedido_aprovacao,
                        // 'comentario_baixa' => $comentario_baixa,
                        // 'descricao' => $descricao,
                    ];
                });
        }


        // $data = [
        //     'baixas' => $baixas_unidade_sanitaria,
        // ];

        // return $this->sendResponse($data, 'Baixas Farmacia!', 200);
        return $baixas_unidade_sanitaria;
    }

    public function verificarBeneficiario(Request $request)
    {
        if (Gate::denies('gerir verificação beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate(['codigo' => 'required|string', 'unidade_sanitaria_id' => 'required|integer']);
        $codigo = $request->codigo;
        $beneficiario = null;
        $dependente_beneficiario = null;
        $cliente = null;
        $unidade_sanitaria_id = $request->unidade_sanitaria_id;


        if (Str::startsWith(Str::upper($codigo), Str::upper('BENE'))) {

            $user = $this->findUserVerificarBeneficiario($codigo);
            if (empty($user))
                return $this->sendError('Usuário não encontrado!', 404);

            $beneficiario = $user->beneficiario;
            $empresa_id = $beneficiario->empresa_id;

            if (empty($beneficiario) || $beneficiario->activo == false)
                return $this->sendError('Beneficiário não encontrado ou inactivo!', 404);

            $associacao_unidade_sanitaria_empresa = $this->unidade_sanitaria->unidadeSanitariaAssociadaAEmpresa($unidade_sanitaria_id, $empresa_id);
            if (empty($associacao_unidade_sanitaria_empresa))
                return $this->sendError('Empresa do Beneficiário não associada!', 404);

            if (!empty($beneficiario->cliente))
                $cliente = $beneficiario->cliente;

            $beneficio_proprio_beneficiario = true;
            $dependente_beneficiario_id = null;

            $beneficiario = [
                'beneficiario_id' => $beneficiario->id,
                'beneficiario_nome' => $beneficiario->nome,
                'beneficiario_telefone' => $beneficiario->telefone,
                'empresa_id' => $beneficiario->empresa_id,
                'empresa_nome' => !empty($beneficiario->empresa) ? $beneficiario->empresa->nome : '',
                'foto_perfil' => !empty($cliente) ? $cliente->foto_perfil : null,
            ];
        } else if (Str::startsWith(Str::upper($codigo), Str::upper('DEBENE'))) {

            $user = $this->findUserVerificarBeneficiario($codigo);
            if (empty($user))
                return $this->sendError('Usuário não encontrado!', 404);

            $dependente_beneficiario = $user->dependenteBeneficiario;
            $beneficiario = $dependente_beneficiario->beneficiario;
            $empresa_id = $dependente_beneficiario->empresa_id;

            if (empty($dependente_beneficiario) || $dependente_beneficiario->activo == false)
                return $this->sendError('Dependente não encontrado ou inactivo!', 404);
            
            if (empty($beneficiario) || $beneficiario->activo == false)
                return $this->sendError('Beneficiário do Dependente não encontrado ou inactivo!', 404);

            $associacao_unidade_sanitaria_empresa = $this->unidade_sanitaria->unidadeSanitariaAssociadaAEmpresa($unidade_sanitaria_id, $empresa_id);
            if (empty($associacao_unidade_sanitaria_empresa))
                return $this->sendError('Empresa do Dependente do Beneficiário não associada!', 404);

            if (!empty($beneficiario->cliente))
                $cliente = $beneficiario->cliente;

            $beneficio_proprio_beneficiario = false;
            $dependente_beneficiario_id = $dependente_beneficiario->id;

            $beneficiario = [
                'beneficiario_id' => $beneficiario->id,
                'beneficiario_nome' => $beneficiario->nome,
                'beneficiario_telefone' => $beneficiario->telefone,
                'empresa_id' => $beneficiario->empresa_id,
                'empresa_nome' => !empty($beneficiario->empresa) ? $beneficiario->empresa->nome : '',
                'foto_perfil' => !empty($cliente) ? $cliente->foto_perfil : null,
            ];

            $dependente_beneficiario = [
                'dependente_beneficiario_id' => $dependente_beneficiario->id,
                'dependente_beneficiario_nome' => $dependente_beneficiario->nome,
                'dependente_beneficiario_telefone' => $dependente_beneficiario->telefone,
                'empresa_id' => $dependente_beneficiario->empresa_id,
                'empresa_nome' => !empty($dependente_beneficiario->empresa) ? $dependente_beneficiario->empresa->nome : '',
                'foto_perfil' => !empty($cliente_dependente) ? $cliente_dependente->foto_perfil : null,
            ];

        } else {
            return $this->sendError('Código informado inválido!', 404);
        }

        $data = [
            'beneficio_proprio_beneficiario' => $beneficio_proprio_beneficiario,
            'beneficiario' => $beneficiario,
            // 'dependente_beneficiario_id' => $dependente_beneficiario_id,
            'dependente_beneficiario' => $dependente_beneficiario,
            'dependente_beneficiario_id' => $dependente_beneficiario_id,
        ];

        return $this->sendResponse($data, '', 200);
    }

    protected function findUserVerificarBeneficiario($codigo)
    {
        $user = User::with(
            [
                'beneficiario:id,activo,nome,telefone,user_id,empresa_id',
                'beneficiario.empresa:id,nome',
                'beneficiario.cliente:id,beneficiario_id,foto_perfil',
                'dependenteBeneficiario:id,activo,nome,telefone,user_id,empresa_id,beneficiario_id',
                'dependenteBeneficiario.beneficiario:id,activo,nome,telefone,user_id,empresa_id',
                'dependenteBeneficiario.beneficiario.cliente:id,beneficiario_id,foto_perfil',
                'dependenteBeneficiario.empresa:id,nome',
                // 'dependenteBeneficiario.cliente:id,dependente_beneficiario_id,foto_perfil'
            ]
        )
            ->where('codigo_login', $codigo)
            ->first();

        return $user;
    }


    public function efectuarBaixa(Request $request)
    {

        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // The request is caming form form-data
        $request['itens_baixa'] = json_decode($request->itens_baixa[0], TRUE);
        $request['beneficio_proprio_beneficiario'] = to_boolean($request->beneficio_proprio_beneficiario);
        $request['id'] = to_integer($request->id);
        $request['dependente_beneficiario_id'] = to_integer($request->dependente_beneficiario_id);

        $request->validate(['accao_codigo' => ['required', 'integer', Rule::in([20, 21, 22, 27])]]);
        $accao_codigo = $request->accao_codigo;
        $request->validate(RulesManager::validacao($accao_codigo));

        if (!empty($request->ficheiros)) {
            if (!validate_files($request, 'ficheiros')) {
                $errors['ficheiros'] = 'Algum dos ficheiros encontrados foi identificado como inválido!';
                return $this->sendErrorValidation($errors, 422);
            }
        }

        $itens_baixa_input = $request->itens_baixa;
        $input_baixa = $request->only(['beneficio_proprio_beneficiario', 'beneficiario_id', 'empresa_id', 'valor', 'nr_comprovativo', 'itens_baixa']);
        $input_baixa['proveniencia'] = 2;
        $input_baixa['dependente_beneficiario_id'] = null;

        if (!$request->beneficio_proprio_beneficiario) {
            $input_baixa['dependente_beneficiario_id'] = $request->dependente_beneficiario_id;
        } else {
            if (isset($request->dependente_beneficiario_id)) {
                $errors['dependente_beneficiario_id'] = ['Foi informado campo o dependente_beneficiario_id tendo sido informado o valor verdadeiro para o campo beneficio_proprio_beneficiario!'];
                return $this->sendErrorValidation($errors, 422);
            }
        }

        $estado_baixa_aguarda_confirmacao = $this->estado_baixa->where('codigo', 10)->first();
        $utilizador_unidade_sanitaria = Auth::user()->utilizadorUnidadeSanitaria;
        if (empty($estado_baixa_aguarda_confirmacao))
            return $this->sendError('Estado da baixa não encontrado. Contacte o Administrador!', 404);

        if (empty($utilizador_unidade_sanitaria))
            return $this->sendError('Utilizador da Unidade Sanitária não encontrado. Contacte o Administrador!', 404);

        $responsavel = [
            'nome' => $utilizador_unidade_sanitaria->nome,
            'accao' => 'Submeteu a Baixa',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $responsavel_temp = [];



        $input_baixa['unidade_sanitaria_id'] = $utilizador_unidade_sanitaria->unidade_sanitaria_id;
        $input_baixa['estado_baixa_id'] = $estado_baixa_aguarda_confirmacao->id;
        $input_baixa['comentario_pedido_aprovacao'] = [];
        // $input_baixa['responsavel'] = [$responsavel];
        $temp_nome_ficheiros = [];
        $unidade_sanitaria_id = $request->unidade_sanitaria_id;
        $baixa_id = $request->id;
        $baixa_unidade_sanitaria = null;
        $objecto_comentario_array = [
            'proveniencia' => 'Unidade Sanitária',
            'nome' => !empty($utilizador_unidade_sanitaria) ? $utilizador_unidade_sanitaria->nome : null,
            'data' => date('d-m-Y H:i:s', strtotime(now())),
            'comentario' => $request->comentario_baixa
        ];

        DB::beginTransaction();
        try {
            if ($accao_codigo == 20) {
                // Submeter Baixa Normal

                $input_baixa['responsavel'] = [];
                $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria->create($input_baixa);

            } else if ($accao_codigo == 21) {
                // Submeter Baixa a partir da Ordem de Reserva

                $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria
                    ->byUnidadeSanitaria($unidade_sanitaria_id)
                    ->with('itensBaixaUnidadeSanitaria')
                    ->find($baixa_id);

                if (empty($baixa_unidade_sanitaria)) {
                    DB::rollback();
                    return $this->sendError('Ordem de Reserva não encontrada!', 404);
                }

                if ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria->isNotEmpty()) {
                    $baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria()->delete();
                }

                // $baixa_unidade_sanitaria->update($input_baixa);
                $baixa_unidade_sanitaria->fill($input_baixa);
            } else if ($accao_codigo == 22) {
                // Submeter Baixa a partir do Pedido de Autorização

                $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria
                    ->byUnidadeSanitaria($unidade_sanitaria_id)
                    ->find($baixa_id);
                if (empty($baixa_unidade_sanitaria)) {
                    DB::rollback();
                    return $this->sendError('Pedido de Autorização não encontrado!', 404);
                }

                // if (is_array($baixa_unidade_sanitaria->responsavel)) {

                //     if (sizeof($baixa_unidade_sanitaria->responsavel) > 0) {
                //         $responsavel_temp = $baixa_unidade_sanitaria->responsavel;
                //         array_push($responsavel_temp, $responsavel);
                //     }
                // }
                // $baixa_unidade_sanitaria->estado_baixa_id = $input_baixa['estado_baixa_id'];
                // $baixa_unidade_sanitaria->responsavel = $responsavel_temp;
                // $baixa_unidade_sanitaria->save();
                $baixa_unidade_sanitaria->fill(['estado_baixa_id' => $input_baixa['estado_baixa_id']]);
            } else if($accao_codigo == 27) {
                // Resubmeter Baixa

                $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria
                    ->byUnidadeSanitaria($unidade_sanitaria_id)
                    ->with('itensBaixaUnidadeSanitaria')
                    ->find($baixa_id);
                if (empty($baixa_unidade_sanitaria)) {
                    DB::rollback();
                    return $this->sendError('Gasto não encontrado', 404);
                }

                if ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria->isNotEmpty()) {
                    $baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria()->delete();
                }

                // $baixa_unidade_sanitaria->update($input_baixa);
                $baixa_unidade_sanitaria->fill($input_baixa);

            } else {
                DB::rollback();
                return $this->sendError('Acção inválida!', 404);
            }

            if ($accao_codigo != 22) {
                foreach ($itens_baixa_input as $key => $iten_baixa_input) {
                    $iten_baixa_input['baixa_unidade_sanitaria_id'] = $baixa_unidade_sanitaria->id;
                    $iten_baixa = new ItenBaixaUnidadeSanitaria();
                    $iten_baixa->create($iten_baixa_input);
                }
            }

            $baixa_unidade_sanitaria->load('unidadeSanitaria');
            // $path = kebab_case($baixa_unidade_sanitaria->unidadeSanitaria->nome) . '-' . $baixa_unidade_sanitaria->unidadeSanitaria->tenant_id . '/baixas/' . $baixa_unidade_sanitaria->id . '/';
            $path = storage_path_u_sanitaria($baixa_unidade_sanitaria->unidadeSanitaria->nome, $baixa_unidade_sanitaria->unidadeSanitaria->id, 'baixas') . "$baixa_unidade_sanitaria->id/";

            if (!empty($baixa_unidade_sanitaria->responsavel)) {

                if (is_array($baixa_unidade_sanitaria->responsavel)) {
                    $responsavel_temp = $baixa_unidade_sanitaria->responsavel;
                    array_push($responsavel_temp, $responsavel);
                } else {
                    array_push($responsavel_temp, $baixa_unidade_sanitaria->responsavel, $responsavel);
                }

                $baixa_unidade_sanitaria->fill(['responsavel' => $responsavel_temp]);
            } else {
                array_push($responsavel_temp, $responsavel);
                $baixa_unidade_sanitaria->fill(['responsavel' => $responsavel_temp]);
            }

            if (!empty($request->comentario_baixa)) {
                $comentario = array();

                if (!empty($baixa_unidade_sanitaria->comentario_baixa)) {
                    $comentario = array();

                    if (is_array($baixa_unidade_sanitaria->comentario_baixa)) {
                        $comentario = $baixa_unidade_sanitaria->comentario_baixa;
                        array_push($comentario, $objecto_comentario_array);
                    } else {
                        array_push($comentario, $baixa_unidade_sanitaria->comentario_baixa, $objecto_comentario_array);
                    }

                    $baixa_unidade_sanitaria->fill(['comentario_baixa' => $comentario]);
                } else {
                    array_push($comentario,  $objecto_comentario_array);
                    $baixa_unidade_sanitaria->fill(['comentario_baixa' => $comentario]);
                }
            }

            if (!empty($request->ficheiros)) {
                foreach ($request->ficheiros as $ficheiro) {
                    $upload = upload_file_s3($path, $ficheiro);
                    if (empty($upload)) {

                        DB::rollBack();
                        if (!empty($path)) {
                            if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                                return $this->sendError('Não foram removidos todos ficheiros!', 500);
                            }
                        }
                        return $this->sendError('Não foi possível efectuar o carregamento do(s) arquivo(s)!', 500);
                    }

                    array_push($temp_nome_ficheiros, $upload);
                }

                // $baixa_unidade_sanitaria->comprovativo = $temp_nome_ficheiros;
                // $baixa_unidade_sanitaria->save();
                $baixa_unidade_sanitaria->fill(['comprovativo' => $temp_nome_ficheiros]);
            }
            $baixa_unidade_sanitaria->save();
            DB::commit();
            return $this->sendSuccess('Baixa efectuada com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    protected function validarPrecoIva($input_baixa)
    {
        $itens_baixa_input = $input_baixa['itens_baixa'];
        $erros = [];
        $valor_total_baixa = 0.00;
        $valor_total_baixa_arredondado = 0.00;

        foreach ($itens_baixa_input as $key => $iten_baixa_input) {

            $preco = (float) ($iten_baixa_input['preco'] * $iten_baixa_input['quantidade']);
            $iva = (float) (($preco * $iten_baixa_input['iva']) / 100);
            $preco_iva = (float) ($preco + $iva);
            $preco_iva_arredondado = round($preco_iva, 2);

            if ($iten_baixa_input['preco_iva'] !== $preco_iva_arredondado) {
                array_push($erros, "O preço com iva na posição $key não corresponde ao valor correcto!");
            }

            $valor_total_baixa = (float) ($valor_total_baixa + $iten_baixa_input['preco_iva']);
            $valor_total_baixa_arredondado = round($valor_total_baixa, 2);
        }

        if ($input_baixa['valor'] !== $valor_total_baixa_arredondado) {
            array_push($erros, "O Valor Total da Venda não corresponde ao somatório correcto de todos itens!");
        }

        return $erros;
    }

    public function getPedidoAprovacao()
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $estados_baixas_pedido_aprovacao = $this->estado_baixa
            ->whereIn('codigo', [7, 8, 9])
            ->pluck('id')
            ->toArray();
        $baixas_unidade_sanitaria = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_pedido_aprovacao);

        $data = [
            'pedidos_aprovacao' => $baixas_unidade_sanitaria,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }
    public function getPedidoAprovacaoExcel()
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $estados_baixas_pedido_aprovacao = $this->estado_baixa
            ->whereIn('codigo', [7, 8, 9])
            ->pluck('id')
            ->toArray();
        $baixas_unidade_sanitaria = $this->getBaixasUnidadeSanitariaByEstadoExcel($estados_baixas_pedido_aprovacao);

        $data = [
            'pedidos_aprovacao' => $baixas_unidade_sanitaria,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }


    public function submeterPedidoAprovacao(Request $request)
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate(['accao_codigo' => ['required', 'integer', Rule::in([40])]]);
        $accao_codigo = $request->accao_codigo;
        $request->validate(RulesManager::validacao($accao_codigo));

        $estado_baixa_aguarda_aprovacaoa_pedido_aprovacao = $this->estado_baixa->where('codigo', 8)->first();
        $utilizador_unidade_sanitaria = Auth::user()->utilizadorUnidadeSanitaria;
        if (empty($estado_baixa_aguarda_aprovacaoa_pedido_aprovacao))
            return $this->sendError('Estado do Peido de Aprovação não encontrado. Contacte o Administrador!', 404);

        if (empty($utilizador_unidade_sanitaria))
            return $this->sendError('Utilizador da Unidade Sanitária não encontrado. Contacte o Administrador!', 404);

        $responsavel = [
            'nome' => $utilizador_unidade_sanitaria->nome,
            'accao' => 'Submeteu a Baixa',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $itens_baixa_input = $request->itens_baixa;
        $input_baixa = $request->only(['beneficio_proprio_beneficiario', 'beneficiario_id', 'empresa_id', 'valor', 'itens_baixa']);
        $input_baixa['proveniencia'] = 2;
        $input_baixa['dependente_beneficiario_id'] = null;
        $input_baixa['unidade_sanitaria_id'] = $utilizador_unidade_sanitaria->unidade_sanitaria_id;
        $input_baixa['estado_baixa_id'] = $estado_baixa_aguarda_aprovacaoa_pedido_aprovacao->id;
        $input_baixa['comentario_pedido_aprovacao'] = [];
        $input_baixa['responsavel'] = [$responsavel];

        if (!$request->beneficio_proprio_beneficiario) {
            $input_baixa['dependente_beneficiario_id'] = $request->dependente_beneficiario_id;
        } else {
            if (isset($request->dependente_beneficiario_id)) {
                $errors['dependente_beneficiario_id'] = ['Foi informado campo o dependente_beneficiario_id tendo sido informado o valor verdadeiro para o campo beneficio_proprio_beneficiario!'];
                return $this->sendErrorValidation($errors, 422);
            }
        }
        // dd($request->all());
        DB::beginTransaction();
        try {
            $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria->create($input_baixa);
            foreach ($itens_baixa_input as $key => $iten_baixa_input) {
                $iten_baixa_input['baixa_unidade_sanitaria_id'] = $baixa_unidade_sanitaria->id;
                $iten_baixa = new ItenBaixaUnidadeSanitaria();
                $iten_baixa->create($iten_baixa_input);
            }
            DB::commit();
            return $this->sendSuccess('Pedido de Aprovação submetido com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }


    /**
     * Delete specified files
     * Auxiliar function
     * @param mixed $path
     * @param mixed $ficheiros
     * @return boolean
     */
    protected function apagarFicheiros($path, $ficheiros)
    {
        if (is_array($ficheiros)) {
            foreach ($ficheiros as $ficheiro) {
                if (!empty($ficheiro)) {
                    if (!delete_file($path . $ficheiro)) {
                        return false;
                    }
                }
            }
        } else {
            if (!empty($ficheiros)) {
                if (!delete_file($path . $ficheiros)) {
                    return false;
                }
            }
        }

        return true;
    }


    /**
     * Download a file of the specied Baixa
     * GET baixas/{id}/comprovativo/download/{ficheiro}
     * @param integer $proveniencia
     * @param integer $baixa_id
     * @param mixed $ficheiro
     * @return Response
     */
    public function downloadComprovativoBaixa($baixa_id, $ficheiro)
    {
        // dd('chegou');
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request()->unidade_sanitaria_id;

        $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria->byUnidadeSanitaria($unidade_sanitaria_id)->with('unidadeSanitaria')->find($baixa_id);
        if (empty($baixa_unidade_sanitaria)) {
            return $this->sendError('Baixa não encontrada!', 404);
        }

        // $path = kebab_case($baixa_unidade_sanitaria->unidadeSanitaria->nome) . '-' . $baixa_unidade_sanitaria->unidadeSanitaria->tenant_id . '/baixas/' . $baixa_unidade_sanitaria->id . '/' . $ficheiro;
        $path = storage_path_u_sanitaria($baixa_unidade_sanitaria->unidadeSanitaria->nome, $baixa_unidade_sanitaria->unidadeSanitaria->id, 'baixas') . "$baixa_unidade_sanitaria->id/$ficheiro";
        return download_file_s3($path);
    }
}
