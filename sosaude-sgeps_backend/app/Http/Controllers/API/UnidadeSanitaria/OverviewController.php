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

class OverviewController extends AppBaseController
{
    //
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

    public function getOverview()
    {
        // $estados_baixas_pedido_aprovacao = $this->estado_baixa
        //     ->whereIn('codigo', [7, 8, 9])
        //     ->pluck('id')
        //     ->toArray();
        // $baixas_unidade_sanitaria = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_pedido_aprovacao);
        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        $unidade_sanitaria = $this->unidade_sanitaria->find($unidade_sanitaria_id);

        $baixas_unidade_sanitaria_total = $this->baixa_unidade_sanitaria
            ->byUnidadeSanitaria($unidade_sanitaria_id)
            ->get();

        $estados_baixas_pedido_aprovacao = $this->estado_baixa
            ->whereIn('codigo', [7])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia_peddidos_rejeitado = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_pedido_aprovacao,0,null,null);

        $estados_baixas_pedido_aprovacao1 = $this->estado_baixa
            ->whereIn('codigo', [9])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia_peddidos_agurardando = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_pedido_aprovacao1,0,null,null);

        $estados_baixas_gasto = $this->estado_baixa
            ->whereIn('codigo', [10])
            ->pluck('id')
            ->toArray();
        $transacoes_aguarda = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto,0,null,null);

        $estados_baixas_gasto_pagamento = $this->estado_baixa
            ->whereIn('codigo', [11])
            ->pluck('id')
            ->toArray();
        $transacoes_pagamento = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto_pagamento,0,null,null);


        $estados_baixas_gasto_pago = $this->estado_baixa
        ->whereIn('codigo', [12])
        ->pluck('id')
        ->toArray();
        $transacoes_pagas = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto_pago,0,null,null);

        $estados_baixas_gasto_rejeitado = $this->estado_baixa
            ->whereIn('codigo', [13])
            ->pluck('id')
            ->toArray();
        $transacoes_rejeitado = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto_rejeitado,0,null,null);


        $transacao_recusada_percent = count($transacoes_rejeitado) == 0 ? 0 : round((count($transacoes_rejeitado)*100)/(count($baixas_unidade_sanitaria_total)), 2);
        $transacao_paga_percent = count($transacoes_pagas) == 0 ? 0 : round((count($transacoes_pagas)*100)/(count($baixas_unidade_sanitaria_total)), 2);
        $transacao_pendente_percent = count($transacoes_aguarda) == 0 ? 0 : round((count($transacoes_aguarda)*100)/(count($baixas_unidade_sanitaria_total)), 2);

        $empresas_lista = $this->getEmpresas($unidade_sanitaria_id);

        $data = [
            'pedidos_aprovacao_rejeitado' => count($baixas_farmacia_peddidos_rejeitado),
            'pedidos_aprovacao_aguardando' => count($baixas_farmacia_peddidos_agurardando),
            'transacoes_aguarda' => count($transacoes_aguarda),
            'transacoes_pagamento' => count($transacoes_pagamento),
            'transacoes_rejeitado' => count($transacoes_rejeitado),
            'transacoes_percent' => [$transacao_recusada_percent,$transacao_paga_percent,$transacao_pendente_percent],
            'empresas' => $empresas_lista,
            'data_criacao_us' => $unidade_sanitaria->created_at
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }

    public function getOverviewFiltered($startDate,$endDate,$empresa_id)
    {
        // $estados_baixas_pedido_aprovacao = $this->estado_baixa
        //     ->whereIn('codigo', [7, 8, 9])
        //     ->pluck('id')
        //     ->toArray();
        // $baixas_unidade_sanitaria = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_pedido_aprovacao);
        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        $empresas_lista = $this->getEmpresas($unidade_sanitaria_id);

        $baixas_unidade_sanitaria_total = $this->baixa_unidade_sanitaria
            ->byUnidadeSanitaria($unidade_sanitaria_id)
            ->get();

        $estados_baixas_pedido_aprovacao = $this->estado_baixa
            ->whereIn('codigo', [7])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia_peddidos_rejeitado = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_pedido_aprovacao,$empresa_id,$startDate,$endDate);

        $estados_baixas_pedido_aprovacao1 = $this->estado_baixa
            ->whereIn('codigo', [9])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia_peddidos_agurardando = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_pedido_aprovacao1,$empresa_id,$startDate,$endDate);

        $estados_baixas_gasto = $this->estado_baixa
            ->whereIn('codigo', [10])
            ->pluck('id')
            ->toArray();
        $transacoes_aguarda = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto,$empresa_id,$startDate,$endDate);

        $estados_baixas_gasto_pagamento = $this->estado_baixa
            ->whereIn('codigo', [11])
            ->pluck('id')
            ->toArray();
        $transacoes_pagamento = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto_pagamento,$empresa_id,$startDate,$endDate);


        $estados_baixas_gasto_pago = $this->estado_baixa
        ->whereIn('codigo', [12])
        ->pluck('id')
        ->toArray();
        $transacoes_pagas = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto_pago,$empresa_id,$startDate,$endDate);

        $estados_baixas_gasto_rejeitado = $this->estado_baixa
            ->whereIn('codigo', [13])
            ->pluck('id')
            ->toArray();
        $transacoes_rejeitado = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto_rejeitado,$empresa_id,$startDate,$endDate);

        $transacao_recusada_percent = count($transacoes_rejeitado) == 0 ? 0 : round((count($transacoes_rejeitado)*100)/(count($baixas_unidade_sanitaria_total)), 2);
        $transacao_paga_percent = count($transacoes_pagas) == 0 ? 0 : round((count($transacoes_pagas)*100)/(count($baixas_unidade_sanitaria_total)), 2);
        $transacao_pendente_percent = count($transacoes_aguarda) == 0 ? 0 : round((count($transacoes_aguarda)*100)/(count($baixas_unidade_sanitaria_total)), 2);

        $data = [
            'pedidos_aprovacao_rejeitado' => count($baixas_farmacia_peddidos_rejeitado),
            'pedidos_aprovacao_aguardando' => count($baixas_farmacia_peddidos_agurardando),
            'transacoes_aguarda' => count($transacoes_aguarda),
            'transacoes_pagamento' => count($transacoes_pagamento),
            'transacoes_rejeitado' => count($transacoes_rejeitado),
            'transacoes_percent' => [$transacao_recusada_percent,$transacao_paga_percent,$transacao_pendente_percent],
            'empresas' => $empresas_lista,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }

    public function getBaixasFarmacia()
    {
        $estados_baixas_gasto = $this->estado_baixa
            ->whereIn('codigo', [10])
            ->pluck('id')
            ->toArray();
        $baixas_unidade_sanitaria = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto,0,null,null);

        $empresas = [];
        // ESTADO AGUARDA CONFIRMACAO
        $gasto_aguarda_confirmacao = [];
        foreach ($baixas_unidade_sanitaria as $baixa) {
            $empresa_nome = $baixa['empresa_nome'];
            $valor_baixa =  (float)$baixa['valor_baixa'];
            if (in_array($empresa_nome, $empresas)) {
                $key = array_search($empresa_nome, $empresas);
                $gasto_aguarda_confirmacao[$key] = $gasto_aguarda_confirmacao[$key] + $valor_baixa;
            } else {
                array_push($empresas, $empresa_nome);
                array_push($gasto_aguarda_confirmacao, $valor_baixa);
            }
        }

        // ESTADO AGUARDA PAGAMENTO
        $estados_baixas_gasto_pagamento = $this->estado_baixa
            ->whereIn('codigo', [11])
            ->pluck('id')
            ->toArray();
        $baixas_unidade_sanitaria_pagamento = $this->getBaixasUnidadeSanitariaByEstado($estados_baixas_gasto_pagamento,0,null,null);

        $empresas_pag = [];
        $gasto_aguarda_pagamento = [];
        foreach ($baixas_unidade_sanitaria_pagamento as $baixa) {
            $empresa_nome = $baixa['empresa_nome'];
            $valor_baixa =  (float)$baixa['valor_baixa'];
            if (in_array($empresa_nome, $empresas_pag)) {
                $key = array_search($empresa_nome, $empresas_pag);
                $gasto_aguarda_pagamento[$key] = $gasto_aguarda_pagamento[$key] + $valor_baixa;
            } else {
                array_push($empresas_pag, $empresa_nome);
                array_push($gasto_aguarda_pagamento, $valor_baixa);
            }
        }


        $data = [
            // 'baixas' => $baixas_farmacia,
            'empresas' => $empresas,
            'gasto_aguarda_confirmacao' => $gasto_aguarda_confirmacao,
            'empresa_gasto_aguarda_pagamento' => $empresas_pag,
            'gasto_aguarda_pagamento' => $gasto_aguarda_pagamento
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }


    public function getBaixasUnidadeSanitariaByEstado(array $estados_baixas,$empId,$startDate,$endDate)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        $baixas_unidade_sanitaria = [];

        if (!empty($estados_baixas) && $empId == 0) {

            $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
                ->byUnidadeSanitaria($unidade_sanitaria_id)
                ->whereIn('estado_baixa_id', $estados_baixas)
                ->with([
                    'beneficiario:id,nome',
                    'empresa:id,nome',
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
                        'empresa_nome' => $baixa_unidade_sanitaria->empresa->nome,
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
        } 
        
        else if(!empty($estados_baixas) && $empId > 0) {

            $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
                ->byUnidadeSanitaria($unidade_sanitaria_id)
                ->where('empresa_id',$empId)
                ->whereBetween('created_at', [$startDate,$endDate])
                ->whereIn('estado_baixa_id', $estados_baixas)
                ->with([
                    'beneficiario:id,nome',
                    'empresa:id,nome',
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
                        'empresa_nome' => $baixa_unidade_sanitaria->empresa->nome,
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
        }else {

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

    public function getEmpresas($usId){
        $empresasLista = DB::table('unidade_sanitarias')
                            ->join('empresa_unidade_sanitaria', 'empresa_unidade_sanitaria.unidade_sanitaria_id', '=', 'unidade_sanitarias.id')
                            ->join('empresas', 'empresas.id', '=', 'empresa_unidade_sanitaria.empresa_id')
                            ->select('empresas.id','empresas.nome')
                            ->where('unidade_sanitarias.id','=',$usId)
                            ->get();

    return $empresasLista;
    }
}
