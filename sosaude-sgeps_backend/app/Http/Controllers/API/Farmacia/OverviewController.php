<?php

namespace App\Http\Controllers\API\Farmacia;

use Carbon\Carbon;
use App\Models\User;
use App\Models\EstadoBaixa;
use App\Models\Medicamento;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BaixaFarmacia;
use App\Models\StockFarmacia;
use Illuminate\Validation\Rule;
use App\Models\FormaMedicamento;
use App\Models\MarcaMedicamento;
use App\Models\ItenBaixaFarmacia;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\AppBaseController;
use App\Helpers\Farmacia\Baixa\RulesManager;
use App\Models\Farmacia;


class OverviewController extends AppBaseController
{
    //

    private $farmacia;
    private $baixa_farmacia;
    private $medicamento;
    private $marca_medicamento;
    private $stock_farmacia;
    private $estado_baixa;

    public function __construct(
        /* StockFarmacia $stock_farmacia, */
        Farmacia $farmacia,
        BaixaFarmacia $baixa_farmacia,
        Medicamento $medicamento,
        MarcaMedicamento $marca_medicamento,
        FormaMedicamento $forma_medicamento,
        StockFarmacia $stock_farmacia,
        EstadoBaixa $estado_baixa
    ) {
        // $this->stock_farmacia = $stock_farmacia;
        $this->farmacia = $farmacia;
        $this->baixa_farmacia = $baixa_farmacia;
        $this->medicamento = $medicamento;
        $this->marca_medicamento = $marca_medicamento;
        $this->stock_farmacia = $stock_farmacia;
        $this->estado_baixa = $estado_baixa;
    }


    public function getOverview()
    {
        $farmacia_id = request('farmacia_id');
        $estado_pedido_aprovacao_codigos = [20];
        $estados_pedido_aprovacao = $this->estado_baixa->whereIn('codigo', $estado_pedido_aprovacao_codigos)->pluck('id');
        // dd($estados_pedido_aprovacao);

        $baixas_farmacia = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('estado_baixa_id', $estados_pedido_aprovacao)
            ->with([
                'itensBaixaFarmacia:id,preco,iva,preco_iva,quantidade,marca_medicamento_id,baixa_farmacia_id',
                'itensBaixaFarmacia.marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                'itensBaixaFarmacia.marcaMedicamento.medicamento:id,codigo,dosagem,nome_generico_medicamento_id,forma_medicamento_id',
                'itensBaixaFarmacia.marcaMedicamento.medicamento.nomeGenerico:id,nome',
                'itensBaixaFarmacia.marcaMedicamento.medicamento.formaMedicamento:id,forma',
            ])
            ->orderBy('updated_at', 'DESC')
            ->get();

            $baixas_farmacia_total = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->get();

            $baixas_farmacia_reject = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('estado_baixa_id', [7])
            ->get();

            $baixas_farmacia_pago = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('estado_baixa_id', [6])
            ->get();


            $baixas_farmacia_pendente = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('estado_baixa_id', [2,3,4,5])
            ->get();

        // PEDIDOS DE AUTORIZACAO

        $estados_baixas_pedido_aprovacao = $this->estado_baixa
            ->whereIn('codigo', [7])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia_peddidos_rejeitado = $this->getBaixasFarmaciaByEstado($estados_baixas_pedido_aprovacao,0,null,null);

        $estados_baixas_pedido_aprovacao1 = $this->estado_baixa
            ->whereIn('codigo', [9])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia_peddidos_agurardando = $this->getBaixasFarmaciaByEstado($estados_baixas_pedido_aprovacao1,0,null,null);

        // BAIXAS (TRANSACOES)

        $estados_baixas_gasto_aguarda = $this->estado_baixa
            ->whereIn('codigo', [10])
            ->pluck('id')
            ->toArray();
        $transacoes_aguarda = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_aguarda,0,null,null);

        $estados_baixas_gasto_pagamento = $this->estado_baixa
            ->whereIn('codigo', [11])
            ->pluck('id')
            ->toArray();
        $transacoes_pagamento = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_pagamento,0,null,null);

        $estados_baixas_gasto_rejeitado = $this->estado_baixa
            ->whereIn('codigo', [13])
            ->pluck('id')
            ->toArray();
        $transacoes_rejeitado = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_rejeitado,0,null,null);

        $estados_baixas_gasto_pago = $this->estado_baixa
        ->whereIn('codigo', [12])
        ->pluck('id')
        ->toArray();
    $transacoes_pagas = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_pago,0,null,null);

        // $transacao_pendente_percent = round((count($baixas_farmacia_peddidos_rejeitado)*100)/(count($baixas_farmacia_total)), 2);
        $transacao_recusada_percent = count($transacoes_rejeitado) == 0 ? 0 : round((count($transacoes_rejeitado)*100)/(count($baixas_farmacia_total)), 2);
        $transacao_paga_percent = count($transacoes_pagas) == 0 ? 0 : round((count($transacoes_pagas)*100)/(count($baixas_farmacia_total)), 2);
        $transacao_pendente_percent = count($transacoes_aguarda) == 0 ? 0 : round((count($transacoes_aguarda)*100)/(count($baixas_farmacia_total)), 2);

    $empresas_lista = $this->getEmpresas($farmacia_id);

        $data = [
            'ordens_reserva' => count($baixas_farmacia),
            'pedidos_aprovacao_rejeitado' => count($baixas_farmacia_peddidos_rejeitado),
            'pedidos_aprovacao_aguardando' => count($baixas_farmacia_peddidos_agurardando),
            'transacoes_aguarda' => count($transacoes_aguarda),
            'transacoes_pagamento' => count($transacoes_pagamento),
            'transacoes_rejeitado' => count($transacoes_rejeitado),
            'transacoes_percent' => [$transacao_recusada_percent,$transacao_paga_percent,$transacao_pendente_percent],
            'empresas' => $empresas_lista
        ];

        return $this->sendResponse($data, 'Ordens de Reserva!', 200);
    }

    public function getOverviewFiltered($startDate,$endDate,$empresa_id){


        $farmacia_id = request('farmacia_id');
        $estado_pedido_aprovacao_codigos = [20];
        $estados_pedido_aprovacao = $this->estado_baixa->whereIn('codigo', $estado_pedido_aprovacao_codigos)->pluck('id');
        // dd($estados_pedido_aprovacao);

        $baixas_farmacia = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('estado_baixa_id', $estados_pedido_aprovacao)
            ->where('empresa_id',$empresa_id)
            ->with([
                'itensBaixaFarmacia:id,preco,iva,preco_iva,quantidade,marca_medicamento_id,baixa_farmacia_id',
                'itensBaixaFarmacia.marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                'itensBaixaFarmacia.marcaMedicamento.medicamento:id,codigo,dosagem,nome_generico_medicamento_id,forma_medicamento_id',
                'itensBaixaFarmacia.marcaMedicamento.medicamento.nomeGenerico:id,nome',
                'itensBaixaFarmacia.marcaMedicamento.medicamento.formaMedicamento:id,forma',
            ])
            ->orderBy('updated_at', 'DESC')
            ->get();
            if($empresa_id != 0){
                $baixas_farmacia_total = $this->baixa_farmacia
                ->byFarmacia($farmacia_id)
                ->where('empresa_id',$empresa_id)
                ->get();
            }
            else{
                $baixas_farmacia_total = $this->baixa_farmacia
                ->byFarmacia($farmacia_id)
                ->get();
            }


            $baixas_farmacia_reject = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('estado_baixa_id', [7])
            ->get();

            $baixas_farmacia_pago = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('estado_baixa_id', [6])
            ->get();


            $baixas_farmacia_pendente = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->whereIn('estado_baixa_id', [2,3,4,5])
            ->get();

        // PEDIDOS DE AUTORIZACAO

        $estados_baixas_pedido_aprovacao = $this->estado_baixa
            ->whereIn('codigo', [7])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia_peddidos_rejeitado = $this->getBaixasFarmaciaByEstado($estados_baixas_pedido_aprovacao,$empresa_id,$startDate,$endDate);

        $estados_baixas_pedido_aprovacao1 = $this->estado_baixa
            ->whereIn('codigo', [9])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia_peddidos_agurardando = $this->getBaixasFarmaciaByEstado($estados_baixas_pedido_aprovacao1,$empresa_id,$startDate,$endDate);

        // BAIXAS (TRANSACOES)

        $estados_baixas_gasto_aguarda = $this->estado_baixa
            ->whereIn('codigo', [10])
            ->pluck('id')
            ->toArray();
        $transacoes_aguarda = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_aguarda,$empresa_id,$startDate,$endDate);

        $estados_baixas_gasto_pagamento = $this->estado_baixa
            ->whereIn('codigo', [11])
            ->pluck('id')
            ->toArray();
        $transacoes_pagamento = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_pagamento,$empresa_id,$startDate,$endDate);

        $estados_baixas_gasto_rejeitado = $this->estado_baixa
            ->whereIn('codigo', [13])
            ->pluck('id')
            ->toArray();
        $transacoes_rejeitado = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_rejeitado,$empresa_id,$startDate,$endDate);

        $estados_baixas_gasto_pago = $this->estado_baixa
        ->whereIn('codigo', [12])
        ->pluck('id')
        ->toArray();
    $transacoes_pagas = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_pago,$empresa_id,$startDate,$endDate);

        // $transacao_pendente_percent = round((count($baixas_farmacia_peddidos_rejeitado)*100)/(count($baixas_farmacia_total)), 2);
        $transacao_recusada_percent = count($transacoes_rejeitado) == 0 ? 0 : round((count($transacoes_rejeitado)*100)/(count($baixas_farmacia_total)), 2);
        $transacao_paga_percent = count($transacoes_pagas) == 0 ? 0 : round((count($transacoes_pagas)*100)/(count($baixas_farmacia_total)), 2);
        $transacao_pendente_percent = count($transacoes_aguarda) == 0 ? 0 : round((count($transacoes_aguarda)*100)/(count($baixas_farmacia_total)), 2);

        
        $data = [
            'ordens_reserva' => count($baixas_farmacia),
            'pedidos_aprovacao_rejeitado' => count($baixas_farmacia_peddidos_rejeitado),
            'pedidos_aprovacao_aguardando' => count($baixas_farmacia_peddidos_agurardando),
            'transacoes_aguarda' => count($transacoes_aguarda),
            'transacoes_pagamento' => count($transacoes_pagamento),
            'transacoes_rejeitado' => count($transacoes_rejeitado),
            'transacoes_percent' => [$transacao_recusada_percent,$transacao_paga_percent,$transacao_pendente_percent]
        ];

        return $this->sendResponse($data, 'Ordens de Reserva!', 200);
    }


    public function getBaixasFarmaciaByEstado(array $estados_baixas_ids,$empId,$startDate,$endDate)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $farmacia_id = request('farmacia_id');
        $baixas_farmacia = [];

        if (!empty($estados_baixas_ids) && $empId == 0) {

            $baixas_farmacia = $this->baixa_farmacia
                ->byFarmacia($farmacia_id)
                ->whereIn('estado_baixa_id', $estados_baixas_ids)
                ->with([
                    'beneficiario:id,nome',
                    'empresa:id,nome',
                    'dependenteBeneficiario:id,nome',
                    'itensBaixaFarmacia:id,preco,iva,preco_iva,quantidade,marca_medicamento_id,baixa_farmacia_id',
                    'itensBaixaFarmacia.marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento:id,codigo,dosagem,nome_generico_medicamento_id,forma_medicamento_id',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento.nomeGenerico:id,nome',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento.formaMedicamento:id,forma',
                ])
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->map(function ($baixa_farmacia) {

                    $descricao = [];
                    foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                        // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                        $descricao_actual = [
                            'id' => $iten_baixa_farmacia->id,
                            'marca_medicamento_id' => $iten_baixa_farmacia->marca_medicamento_id,
                            'marca' => !empty($iten_baixa_farmacia->marcaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->marca : '',
                            'medicamento_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->codigo : '',
                            'medicamento_nome_generico' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico->nome : '',
                            'forma' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma : '',
                            'dosagem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->dosagem : '',
                            'quantidade' => $iten_baixa_farmacia->quantidade,
                            'preco' => $iten_baixa_farmacia->preco,
                            'iva' => $iten_baixa_farmacia->iva,
                            'preco_iva' => $iten_baixa_farmacia->preco_iva,
                        ];
                        array_push($descricao, $descricao_actual);
                        $descricao_actual = [];
                    }


                    $responsavel = $baixa_farmacia->responsavel;
                    $comentario_baixa = $baixa_farmacia->comentario_baixa;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    return [
                        'id' => $baixa_farmacia->id,
                        'empresa_id' => $baixa_farmacia->empresa_id,
                        'empresa_nome' => $baixa_farmacia->empresa->nome,
                        'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                        'beneficiario_id' => $baixa_farmacia->beneficiario_id,
                        'dependente_beneficiario_id' => $baixa_farmacia->dependente_beneficiario_id,
                        'proveniencia' => $baixa_farmacia->proveniencia,
                        'nome_beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                        'nome_dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                        'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                        'valor_baixa' => $baixa_farmacia->valor,
                        'comprovativo' => $baixa_farmacia->comprovativo,
                        'nr_comprovativo' => $baixa_farmacia->nr_comprovativo,
                        // 'estado' => $baixa_farmacia->estado,
                        'estado_id' => $baixa_farmacia->estadoBaixa->id,
                        'estado_codigo' => $baixa_farmacia->estadoBaixa->codigo,
                        'estado_nome' => $baixa_farmacia->estadoBaixa->nome,
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'data_baixa' => empty($baixa_farmacia->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->created_at)),
                        'updated_at' => empty($baixa_farmacia->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->updated_at)),
                        'data_criacao_pedido_aprovacao' => empty($baixa_farmacia->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_criacao_pedido_aprovacao)),
                        'data_aprovacao_pedido_aprovacao' => empty($baixa_farmacia->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_aprovacao_pedido_aprovacao)),
                        'responsavel' => $responsavel,
                        'resposavel_aprovacao_pedido_aprovacao' => $baixa_farmacia->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                        'comentario_baixa' => $comentario_baixa,
                        'descricao' => $descricao,
                    ];
                });
        }
        
        else if(!empty($estados_baixas_ids) && $empId > 0) {

            $baixas_farmacia = $this->baixa_farmacia
                ->byFarmacia($farmacia_id)
                ->where('empresa_id',$empId)
                ->whereBetween('created_at', [$startDate,$endDate])
                ->whereIn('estado_baixa_id', $estados_baixas_ids)
                
                ->with([
                    'beneficiario:id,nome',
                    'empresa:id,nome',
                    'dependenteBeneficiario:id,nome',
                    'itensBaixaFarmacia:id,preco,iva,preco_iva,quantidade,marca_medicamento_id,baixa_farmacia_id',
                    'itensBaixaFarmacia.marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento:id,codigo,dosagem,nome_generico_medicamento_id,forma_medicamento_id',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento.nomeGenerico:id,nome',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento.formaMedicamento:id,forma',
                ])
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->map(function ($baixa_farmacia) {

                    $descricao = [];
                    foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                        // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                        $descricao_actual = [
                            'id' => $iten_baixa_farmacia->id,
                            'marca_medicamento_id' => $iten_baixa_farmacia->marca_medicamento_id,
                            'marca' => !empty($iten_baixa_farmacia->marcaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->marca : '',
                            'medicamento_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->codigo : '',
                            'medicamento_nome_generico' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico->nome : '',
                            'forma' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma : '',
                            'dosagem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->dosagem : '',
                            'quantidade' => $iten_baixa_farmacia->quantidade,
                            'preco' => $iten_baixa_farmacia->preco,
                            'iva' => $iten_baixa_farmacia->iva,
                            'preco_iva' => $iten_baixa_farmacia->preco_iva,
                        ];
                        array_push($descricao, $descricao_actual);
                        $descricao_actual = [];
                    }


                    $responsavel = $baixa_farmacia->responsavel;
                    $comentario_baixa = $baixa_farmacia->comentario_baixa;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    return [
                        'id' => $baixa_farmacia->id,
                        'empresa_id' => $baixa_farmacia->empresa_id,
                        'empresa_nome' => $baixa_farmacia->empresa->nome,
                        'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                        'beneficiario_id' => $baixa_farmacia->beneficiario_id,
                        'dependente_beneficiario_id' => $baixa_farmacia->dependente_beneficiario_id,
                        'proveniencia' => $baixa_farmacia->proveniencia,
                        'nome_beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                        'nome_dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                        'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                        'valor_baixa' => $baixa_farmacia->valor,
                        'comprovativo' => $baixa_farmacia->comprovativo,
                        'nr_comprovativo' => $baixa_farmacia->nr_comprovativo,
                        // 'estado' => $baixa_farmacia->estado,
                        'estado_id' => $baixa_farmacia->estadoBaixa->id,
                        'estado_codigo' => $baixa_farmacia->estadoBaixa->codigo,
                        'estado_nome' => $baixa_farmacia->estadoBaixa->nome,
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'data_baixa' => empty($baixa_farmacia->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->created_at)),
                        'updated_at' => empty($baixa_farmacia->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->updated_at)),
                        'data_criacao_pedido_aprovacao' => empty($baixa_farmacia->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_criacao_pedido_aprovacao)),
                        'data_aprovacao_pedido_aprovacao' => empty($baixa_farmacia->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_aprovacao_pedido_aprovacao)),
                        'responsavel' => $responsavel,
                        'resposavel_aprovacao_pedido_aprovacao' => $baixa_farmacia->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                        'comentario_baixa' => $comentario_baixa,
                        'descricao' => $descricao,
                    ];
                });
        }else {

            $baixas_farmacia = $this->baixa_farmacia
                ->byFarmacia($farmacia_id)
                ->with([
                    'beneficiario:id,nome',
                    'dependenteBeneficiario:id,nome',
                    'itensBaixaFarmacia:id,preco,iva,preco_iva,quantidade,marca_medicamento_id,baixa_farmacia_id',
                    'itensBaixaFarmacia.marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento:id,codigo,dosagem,nome_generico_medicamento_id,forma_medicamento_id',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento.nomeGenerico:id,nome',
                    'itensBaixaFarmacia.marcaMedicamento.medicamento.formaMedicamento:id,forma',
                ])
                ->orderBy('updated_at', 'DESC')
                ->get()
                ->map(function ($baixa_farmacia) {

                    $descricao = [];
                    foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                        // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                        $descricao_actual = [
                            'id' => $iten_baixa_farmacia->id,
                            'marca_medicamento_id' => $iten_baixa_farmacia->marca_medicamento_id,
                            'marca' => !empty($iten_baixa_farmacia->marcaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->marca : '',
                            'medicamento_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->codigo : '',
                            'medicamento_nome_generico' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico->nome : '',
                            'forma' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma : '',
                            'dosagem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->dosagem : '',
                            'quantidade' => $iten_baixa_farmacia->quantidade,
                            'preco' => $iten_baixa_farmacia->preco,
                            'iva' => $iten_baixa_farmacia->iva,
                            'preco_iva' => $iten_baixa_farmacia->preco_iva,
                        ];
                        array_push($descricao, $descricao_actual);
                        $descricao_actual = [];
                    }


                    $responsavel = $baixa_farmacia->responsavel;
                    $comentario_baixa = $baixa_farmacia->comentario_baixa;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    return [
                        'id' => $baixa_farmacia->id,
                        'empresa_id' => $baixa_farmacia->empresa_id,
                        'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                        'beneficiario_id' => $baixa_farmacia->beneficiario_id,
                        'dependente_beneficiario_id' => $baixa_farmacia->dependente_beneficiario_id,
                        'proveniencia' => $baixa_farmacia->proveniencia,
                        'nome_beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                        'nome_dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                        'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                        'valor_baixa' => $baixa_farmacia->valor,
                        'comprovativo' => $baixa_farmacia->comprovativo,
                        'nr_comprovativo' => $baixa_farmacia->nr_comprovativo,
                        // 'estado' => $baixa_farmacia->estado,
                        'estado_id' => $baixa_farmacia->estadoBaixa->id,
                        'estado_codigo' => $baixa_farmacia->estadoBaixa->codigo,
                        'estado_nome' => $baixa_farmacia->estadoBaixa->nome,
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'data_baixa' => empty($baixa_farmacia->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->created_at)),
                        'updated_at' => empty($baixa_farmacia->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->updated_at)),
                        'data_criacao_pedido_aprovacao' => empty($baixa_farmacia->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_criacao_pedido_aprovacao)),
                        'data_aprovacao_pedido_aprovacao' => empty($baixa_farmacia->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_aprovacao_pedido_aprovacao)),
                        'responsavel' => $responsavel,
                        'resposavel_aprovacao_pedido_aprovacao' => $baixa_farmacia->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                        'comentario_baixa' => $comentario_baixa,
                        'descricao' => $descricao,
                    ];
                });
        }



        // dd($baixas_farmacia);
        // $data = [
        //     'baixas' => $baixas_farmacia,
        // ];

        // return $this->sendResponse($data, 'Baixas Farmacia!', 200);
        return $baixas_farmacia;
    }

    public function getBaixasFarmacia()
    {
        $estados_baixas_gasto = $this->estado_baixa
            ->whereIn('codigo', [10, 11])
            ->pluck('id')
            ->toArray();
        $baixas_farmacia = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto,0,null,null);

        $empresas = [];
        // ESTADO AGUARDA CONFIRMACAO
        $gasto_aguarda_confirmacao = [];
        foreach ($baixas_farmacia as $baixa) {
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
        $baixas_farmacia_pagamento = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto_pagamento,0,null,null);

        $empresas_pag = [];
        $gasto_aguarda_pagamento = [];
        foreach ($baixas_farmacia_pagamento as $baixa) {
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

    public function getEmpresas($farmId){
        $empresasLista = DB::table('farmacias')
                            ->join('empresa_farmacia', 'empresa_farmacia.farmacia_id', '=', 'farmacias.id')
                            ->join('empresas', 'empresas.id', '=', 'empresa_farmacia.empresa_id')
                            ->select('empresas.id','empresas.nome')
                            ->where('farmacias.id','=',$farmId)
                            ->get();

    return $empresasLista;
    }

}
