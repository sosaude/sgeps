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

class BaixaFarmaciaAPIController extends AppBaseController
{
    // private $stock_farmacia;
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


    public function getBaixasFarmacia()
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        
        $estados_baixas_gasto = $this->estado_baixa
            ->whereIn('codigo', [10, 11, 12, 13])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia = $this->getBaixasFarmaciaByEstado($estados_baixas_gasto);
        $data = [
            'baixas' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }
    public function getBaixasFarmaciaExcel()
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        
        $estados_baixas_gasto = $this->estado_baixa
            ->whereIn('codigo', [10, 11, 12, 13])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia = $this->getBaixasFarmaciaByEstadoExcel($estados_baixas_gasto);
        $data = [
            'baixas' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }


    public function getBaixasFarmaciaByEstadoExcel(array $estados_baixas_ids)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $farmacia_id = request('farmacia_id');
        $baixas_farmacia = [];

        if (!empty($estados_baixas_ids)) {

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
                        // 'id' => $baixa_farmacia->id,
                        // 'empresa_id' => $baixa_farmacia->empresa_id,
                        'Empresa' =>$baixa_farmacia->empresa->nome,
                        // 'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                        // 'beneficiario_id' => $baixa_farmacia->beneficiario_id,
                        // 'dependente_beneficiario_id' => $baixa_farmacia->dependente_beneficiario_id,
                        // 'proveniencia' => $baixa_farmacia->proveniencia,
                        'Nome Beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                        'Nome Dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                        'Nome instituição' => $baixa_farmacia->farmacia->nome,
                        'Valor' => $baixa_farmacia->valor,
                        // 'comprovativo' => $baixa_farmacia->comprovativo,
                        'Nº comprovativo' => $baixa_farmacia->nr_comprovativo,
                        // 'estado' => $baixa_farmacia->estado,
                        // 'estado_id' => $baixa_farmacia->estadoBaixa->id,
                        // 'estado_codigo' => $baixa_farmacia->estadoBaixa->codigo,
                        'Estado' => $baixa_farmacia->estadoBaixa->nome,
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'Data baixa' => empty($baixa_farmacia->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->created_at)),
                        // 'updated_at' => empty($baixa_farmacia->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->updated_at)),
                        // 'data_criacao_pedido_aprovacao' => empty($baixa_farmacia->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_criacao_pedido_aprovacao)),
                        // 'data_aprovacao_pedido_aprovacao' => empty($baixa_farmacia->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_aprovacao_pedido_aprovacao)),
                        // 'responsavel' => $responsavel,
                        // 'resposavel_aprovacao_pedido_aprovacao' => $baixa_farmacia->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                        // 'comentario_baixa' => $comentario_baixa,
                        // 'descricao' => $descricao,
                    ];
                });
        } else {

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
                        // 'id' => $baixa_farmacia->id,
                        // 'empresa_id' => $baixa_farmacia->empresa_id,
                        'Empresa' =>$baixa_farmacia->empresa->nome,
                        // 'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                        // 'beneficiario_id' => $baixa_farmacia->beneficiario_id,
                        // 'dependente_beneficiario_id' => $baixa_farmacia->dependente_beneficiario_id,
                        // 'proveniencia' => $baixa_farmacia->proveniencia,
                        'Nome Beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                        'Nome Dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                        'Nome instituição' => $baixa_farmacia->farmacia->nome,
                        'Valor' => $baixa_farmacia->valor,
                        // 'comprovativo' => $baixa_farmacia->comprovativo,
                        'Nº comprovativo' => $baixa_farmacia->nr_comprovativo,
                        // 'estado' => $baixa_farmacia->estado,
                        // 'estado_id' => $baixa_farmacia->estadoBaixa->id,
                        // 'estado_codigo' => $baixa_farmacia->estadoBaixa->codigo,
                        'Estado' => $baixa_farmacia->estadoBaixa->nome,
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        'Data baixa' => empty($baixa_farmacia->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->created_at)),
                        // 'updated_at' => empty($baixa_farmacia->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->updated_at)),
                        // 'data_criacao_pedido_aprovacao' => empty($baixa_farmacia->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_criacao_pedido_aprovacao)),
                        // 'data_aprovacao_pedido_aprovacao' => empty($baixa_farmacia->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_aprovacao_pedido_aprovacao)),
                        // 'responsavel' => $responsavel,
                        // 'resposavel_aprovacao_pedido_aprovacao' => $baixa_farmacia->resposavel_aprovacao_pedido_aprovacao,
                        // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                        // 'comentario_baixa' => $comentario_baixa,
                        // 'descricao' => $descricao,
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

    public function getBaixasFarmaciaByEstado(array $estados_baixas_ids)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $farmacia_id = request('farmacia_id');
        $baixas_farmacia = [];

        if (!empty($estados_baixas_ids)) {

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
                        'empresa_nome' =>$baixa_farmacia->empresa->nome,
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
        } else {

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

    public function verificarBeneficiario(Request $request)
    {
        if (Gate::denies('gerir verificação beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }


        $request->validate(['codigo' => 'required|string', 'farmacia_id' => 'required|integer']);
        $codigo = $request->codigo;
        $cliente = null;
        $beneficiario = null;
        $dependente_beneficiario = null;
        $farmacia_id = $request->farmacia_id;
        // dd($farmacia_id);

        if (Str::startsWith(Str::upper($codigo), Str::upper('BENE'))) {

            $user = $this->findUserVerificarBeneficiario($codigo);
            if (empty($user))
                return $this->sendError('Usuário não encontrado!', 404);

            $beneficiario = $user->beneficiario;
            $empresa_id = $beneficiario->empresa_id;

            if (empty($beneficiario) || $beneficiario->activo == false)
                return $this->sendError('Beneficiário não encontrado ou inactivo!', 404);

            $associacao_farmacia_empresa = $this->farmacia->farmaciaAssociadaAEmpresa($farmacia_id, $empresa_id);
            if (empty($associacao_farmacia_empresa))
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
            $empresa_id = $beneficiario->empresa_id;

            if (empty($dependente_beneficiario) || $dependente_beneficiario->activo == false)
                return $this->sendError('Dependente não encontrado ou inactivo!', 404);


            if (empty($beneficiario) || $beneficiario->activo == false)
                return $this->sendError('Beneficiário do Dependente não encontrado ou inactivo!', 404);

            $associacao_farmacia_empresa = $this->farmacia->farmaciaAssociadaAEmpresa($farmacia_id, $empresa_id);
            if (empty($associacao_farmacia_empresa))
                return $this->sendError('Empresa do Beneficiário não associada!', 404);

            if (!empty($dependente_beneficiario->cliente))
                $cliente_dependente = $dependente_beneficiario->cliente;

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
        // The request is caming form form-data
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

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
        $input_baixa['proveniencia'] = 1;
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
        $utilizador_farmacia = Auth::user()->utilizadorFarmacia;
        $responsavel = [
            'nome' => $utilizador_farmacia->nome,
            'accao' => 'Submeteu a Baixa',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $responsavel_temp = array();

        if (empty($estado_baixa_aguarda_confirmacao))
            return $this->sendError('Estado da baixa não encontrado. Contacte o Administrador!', 404);

        if (empty($utilizador_farmacia))
            return $this->sendError('Utilizador da Farmácia não encontrado. Contacte o Administrador!', 404);



        $input_baixa['farmacia_id'] = $utilizador_farmacia->farmacia_id;
        $input_baixa['estado_baixa_id'] = $estado_baixa_aguarda_confirmacao->id;
        $input_baixa['comentario_pedido_aprovacao'] = [];
        // $input_baixa['responsavel'] = [$responsavel];

        $temp_nome_ficheiros = [];
        $farmacia_id = $request->farmacia_id;
        $baixa_id = $request->id;
        $baixa_farmacia = null;
        $objecto_comentario_array = [
            'proveniencia' => 'Farmácia',
            'nome' => !empty($utilizador_farmacia) ? $utilizador_farmacia->nome : null,
            'data' => date('d-m-Y H:i:s', strtotime(now())),
            'comentario' => $request->comentario_baixa
        ];
        // dd($request->all());
        DB::beginTransaction();
        try {
            if ($accao_codigo == 20) {
                // Submeter Baixa Normal
                $input_baixa['responsavel'] = [];
                $input_baixa['comentario_baixa'] = [];
                $baixa_farmacia = $this->baixa_farmacia->create($input_baixa);
            } else if ($accao_codigo == 21) {
                // Submeter Baixa a partir da Ordem de Reserva
                $baixa_farmacia = $this->baixa_farmacia
                    ->byFarmacia($farmacia_id)
                    ->with('itensBaixaFarmacia')
                    ->find($baixa_id);

                if (empty($baixa_farmacia)) {
                    DB::rollback();
                    return $this->sendError('Ordem de Reserva não encontrada!', 404);
                }

                if ($baixa_farmacia->itensBaixaFarmacia->isNotEmpty()) {
                    $baixa_farmacia->itensBaixaFarmacia()->delete();
                }

                $baixa_farmacia->fill($input_baixa);
            } else if ($accao_codigo == 22) {
                // Submeter Baixa a partir do Pedido de Autorização

                $baixa_farmacia = $this->baixa_farmacia->find($baixa_id);
                if (empty($baixa_farmacia)) {
                    DB::rollback();
                    return $this->sendError('Pedido de Autorização não encontrado!', 404);
                }
                $baixa_farmacia->fill(['estado_baixa_id' => $input_baixa['estado_baixa_id']]);

                if($baixa_farmacia->itensBaixaFarmacia->isNotEmpty()) {
                    foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa) {

                        // O abate é feito aqui para o Pedido de Autorização, pois no loop geral de abate mais abaixo não é abrangido o Pedido de Autorização
                        $abate_stock = $this->abateItenStock($farmacia_id, $iten_baixa->marca_medicamento_id, $iten_baixa->quantidade);

                        if (is_array($abate_stock)) {
                            if (array_key_exists('encontrado', $abate_stock)) {
                                return $this->sendError('Item não encontrado no Stock da Farmácia', 404);
                            } else if(array_key_exists('quantidade', $abate_stock)) {
                                return $this->sendError('Quantidade solicitada maior que a quantidade disponível', 404);
                            }
                        }
                    }
                }
            } else if ($accao_codigo == 27) {
                // Resubmeter Baixa

                $baixa_farmacia = $this->baixa_farmacia
                    ->byFarmacia($farmacia_id)
                    ->with('itensBaixaFarmacia')
                    ->find($baixa_id);
                if (empty($baixa_farmacia)) {
                    DB::rollback();
                    return $this->sendError('Gasto não encontrado', 404);
                }

                if ($baixa_farmacia->itensBaixaFarmacia->isNotEmpty()) {

                    foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa) {
                        // Faz-se a reposição e logo abaixo é feito o abate no loop geral do abate, isso acontece porque a quando da submissão que terá antecedido a devolução, foi feito um abate e de lá para cá nenhuma reposição a quando da devolução
                        $repor_stock = $this->reporItenStock($farmacia_id, $iten_baixa->marca_medicamento_id, $iten_baixa->quantidade);
                        $iten_baixa->delete();
                    }
                }

                $baixa_farmacia->fill($input_baixa);
            } else {

                DB::rollback();
                return $this->sendError('Acção inválida!', 404);
            }

            if ($accao_codigo != 22) {
                foreach ($itens_baixa_input as $key => $iten_baixa_input) {
                    $iten_baixa_input['baixa_farmacia_id'] = $baixa_farmacia->id;
                    $iten_baixa = new ItenBaixaFarmacia();
                    $iten_baixa->create($iten_baixa_input);
                    $abate_stock = $this->abateItenStock($farmacia_id, $iten_baixa_input['marca_medicamento_id'], $iten_baixa_input['quantidade']);

                    if (is_array($abate_stock)) {
                        if (array_key_exists('encontrado', $abate_stock)) {
                            return $this->sendError('Item não encontrado no Stock da Farmácia', 404);
                        } else if(array_key_exists('quantidade', $abate_stock)) {
                            return $this->sendError('Quantidade solicitada maior que a quantidade disponível', 404);
                        }
                    }

                }
            }

            $baixa_farmacia->load('farmacia');
            // $path = kebab_case($baixa_farmacia->farmacia->nome) . '-' . $baixa_farmacia->farmacia->tenant_id . '/baixas/' . $baixa_farmacia->id . '/';
            $path = storage_path_farmacia($baixa_farmacia->farmacia->nome, $baixa_farmacia->farmacia->id, 'baixas') . "$baixa_farmacia->id/";

            if (!empty($baixa_farmacia->responsavel)) {

                if (is_array($baixa_farmacia->responsavel)) {
                    $responsavel_temp = $baixa_farmacia->responsavel;
                    array_push($responsavel_temp, $responsavel);
                } else {
                    array_push($responsavel_temp, $baixa_farmacia->responsavel, $responsavel);
                }

                $baixa_farmacia->fill(['responsavel' => $responsavel_temp]);
            } else {
                array_push($responsavel_temp, $responsavel);
                $baixa_farmacia->fill(['responsavel' => $responsavel_temp]);
            }

            if (!empty($request->comentario_baixa)) {
                $comentario = array();

                if (!empty($baixa_farmacia->comentario_baixa)) {
                    $comentario = array();

                    if (is_array($baixa_farmacia->comentario_baixa)) {
                        $comentario = $baixa_farmacia->comentario_baixa;
                        array_push($comentario, $objecto_comentario_array);
                    } else {
                        array_push($comentario, $baixa_farmacia->comentario_baixa, $objecto_comentario_array);
                    }

                    $baixa_farmacia->fill(['comentario_baixa' => $comentario]);
                } else {
                    array_push($comentario,  $objecto_comentario_array);
                    $baixa_farmacia->fill(['comentario_baixa' => $comentario]);
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

                $baixa_farmacia->fill(['comprovativo' => $temp_nome_ficheiros]);
            }

            $baixa_farmacia->save();
            DB::commit();
            return $this->sendSuccess('Baixa efectuada com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            if (!empty($path)) {
                if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                    return $this->sendError('Não foram removidos todos ficheiros! ' . $e->getMessage(), 500);
                }
            }
            return $this->sendError($e->getTrace(), 500);
        }
    }

    protected function validarPrecoIva($input_baixa)
    {
        $itens_baixa_input = $input_baixa['itens_baixa'];
        $erros = [];
        $valor_total_baixa = 0.00;
        // $valor_total_baixa_arredondado = 0.00;

        foreach ($itens_baixa_input as $key => $iten_baixa_input) {

            $preco = round((float) ($iten_baixa_input['preco'] * $iten_baixa_input['quantidade']), 2);
            $iva = round((float) (($preco * $iten_baixa_input['iva']) / 100), 2);
            $preco_iva = round((float) ($preco + $iva), 2);
            // $preco_iva_arredondado = round($preco_iva, 2);

            if ($iten_baixa_input['preco_iva'] !== $preco_iva) {
                array_push($erros, "O preço com iva na posição $key não corresponde ao valor correcto!");
            }

            $valor_total_baixa = round((float) ($valor_total_baixa + $iten_baixa_input['preco_iva']), 2);
            // $valor_total_baixa_arredondado = round($valor_total_baixa, 2);
        }

        if ($input_baixa['valor'] !== $valor_total_baixa) {
            array_push($erros, "O Valor Total da Venda não corresponde ao somatório correcto de todos itens!");
        }

        return $erros;
    }

    public function getPedidoAprovacao()
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        // dd($baixas_farmacia);
        $estados_baixas_pedido_aprovacao = $this->estado_baixa
            ->whereIn('codigo', [7, 8, 9])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia = $this->getBaixasFarmaciaByEstado($estados_baixas_pedido_aprovacao);

        $data = [
            'pedidos_aprovacao' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }
    public function getPedidoAprovacaoExcel()
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        // dd($baixas_farmacia);
        $estados_baixas_pedido_aprovacao = $this->estado_baixa
            ->whereIn('codigo', [7, 8, 9])
            ->pluck('id')
            ->toArray();
        // dd($estados_baixas_gasto);
        $baixas_farmacia = $this->getBaixasFarmaciaByEstadoExcel($estados_baixas_pedido_aprovacao);

        $data = [
            'pedidos_aprovacao' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia!', 200);
    }

    public function submeterPedidoAprovacao(Request $request)
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        /* $request['beneficio_proprio_beneficiario'] = to_boolean($request->beneficio_proprio_beneficiario);
        $request['dependente_beneficiario_id'] = to_boolean($request->dependente_beneficiario_id); */

        $request->validate(['accao_codigo' => ['required', 'integer', Rule::in([40])]]);
        $accao_codigo = $request->accao_codigo;
        $request->validate(RulesManager::validacao($accao_codigo));
        // dd($request->all());

        $estado_baixa_aguarda_aprovacaoa_pedido_aprovacao = $this->estado_baixa->where('codigo', 8)->first();
        $utilizador_farmacia = Auth::user()->utilizadorFarmacia;
        if (empty($estado_baixa_aguarda_aprovacaoa_pedido_aprovacao))
            return $this->sendError('Estado do Pedido de Aprovação não encontrado. Contacte o Administrador!', 404);

        if (empty($utilizador_farmacia))
            return $this->sendError('Utilizador da Farmácia não encontrado. Contacte o Administrador!', 404);

        $responsavel = [
            'nome' => $utilizador_farmacia->nome,
            'accao' => 'Submeteu o Pedido de Autorizaçaõ',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $itens_baixa_input = $request->itens_baixa;
        $input_baixa = $request->only(['beneficio_proprio_beneficiario', 'beneficiario_id', 'empresa_id', 'valor', 'itens_baixa']);
        $input_baixa['proveniencia'] = 1;
        $input_baixa['dependente_beneficiario_id'] = null;
        $input_baixa['farmacia_id'] = $utilizador_farmacia->farmacia_id;
        $input_baixa['estado_baixa_id'] = $estado_baixa_aguarda_aprovacaoa_pedido_aprovacao->id;
        $input_baixa['data_criacao_pedido_aprovacao'] = now();
        $input_baixa['comentario_baixa'] = [];
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

        if (!empty($request->comentario)) {

            $objecto_comentario_array = [
                'proveniencia' => 'Mobile',
                'nome' => $utilizador_farmacia->nome,
                'data' => date('d-m-Y H:i:s', strtotime(now())),
                'comentario' => $request->comentario
            ];

            $comentario = [];
            array_push($comentario, $objecto_comentario_array);
            $input_baixa['comentario_baixa'] = $comentario;
        }

        DB::beginTransaction();
        try {
            $baixa_farmacia = $this->baixa_farmacia->create($input_baixa);
            foreach ($itens_baixa_input as $key => $iten_baixa_input) {
                $iten_baixa_input['baixa_farmacia_id'] = $baixa_farmacia->id;
                $iten_baixa = new ItenBaixaFarmacia();
                $iten_baixa->create($iten_baixa_input);
            }
            DB::commit();
            return $this->sendSuccess('Pedido de Aprovação submetido com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }



    public function getOrdemReservaBeneficiario($beneficiario_id)
    {
        if (Gate::denies('gerir ordem reserva')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $farmacia_id = request('farmacia_id');
        $estado_pedido_aprovacao_codigos = [20];
        $estados_pedido_aprovacao = $this->estado_baixa->whereIn('codigo', $estado_pedido_aprovacao_codigos)->pluck('id');
        // dd($estados_pedido_aprovacao);


        $baixas_farmacia = $this->baixa_farmacia
            ->byFarmacia($farmacia_id)
            ->where('beneficiario_id', $beneficiario_id)
            ->whereIn('estado_baixa_id', $estados_pedido_aprovacao)
            ->with([
                'itensBaixaFarmacia:id,preco,iva,preco_iva,quantidade,marca_medicamento_id,baixa_farmacia_id',
                'itensBaixaFarmacia.marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                'itensBaixaFarmacia.marcaMedicamento.medicamento:id,codigo,dosagem,nome_generico_medicamento_id,forma_medicamento_id',
                'itensBaixaFarmacia.marcaMedicamento.medicamento.nomeGenerico:id,nome',
                'itensBaixaFarmacia.marcaMedicamento.medicamento.formaMedicamento:id,forma',
            ])
            ->get()
            ->map(function ($baixa_farmacia) {
                $itens_ordem_reserva = [];

                foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                    // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                    $iten_ordem_reserva = [
                        'id' => $iten_baixa_farmacia->id,
                        'marca_medicamento_id' => $iten_baixa_farmacia->marca_medicamento_id,
                        'marca' => !empty($iten_baixa_farmacia->marcaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->marca : '',
                        'marca_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->codigo : '',
                        'medicamento_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->codigo : '',
                        'medicamento_pais_origem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->pais_origem : '',
                        'medicamento_nome_generico' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico->nome : '',
                        'medicamento_forma' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma : '',
                        'medicamento_dosagem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->dosagem : '',
                        'quantidade' => $iten_baixa_farmacia->quantidade,
                        'preco' => $iten_baixa_farmacia->preco,
                        'iva' => $iten_baixa_farmacia->iva,
                        'preco_iva' => $iten_baixa_farmacia->preco_iva,
                    ];
                    array_push($itens_ordem_reserva, $iten_ordem_reserva);
                    $iten_ordem_reserva = [];
                }


                $responsavel = $baixa_farmacia->responsavel;
                $comentario_baixa = $baixa_farmacia->comentario_baixa;

                if (is_array($responsavel))
                    usort($responsavel, sort_desc_array_objects('data'));

                if (is_array($comentario_baixa))
                    usort($comentario_baixa, sort_desc_array_objects('data'));

                return [
                    'id' => $baixa_farmacia->id,
                    'proveniencia' => $baixa_farmacia->proveniencia,
                    'empresa_id' => $baixa_farmacia->empresa_id,
                    'beneficiario_id' => $baixa_farmacia->beneficiario->id,
                    'nome_beneficiario' => $baixa_farmacia->beneficiario->nome,
                    'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                    'valor_baixa' => $baixa_farmacia->valor,
                    'nr_comprovativo' => $baixa_farmacia->nr_comprovativo,
                    'comprovativo' => $baixa_farmacia->comprovativo,
                    'responsavel' => $responsavel,
                    'comentario_baixa' => $comentario_baixa,
                    // 'estado' => $baixa_farmacia->estado,
                    // 'estado_id' => $baixa_farmacia->estadoBaixa->id,
                    'estado_codigo' => $baixa_farmacia->estadoBaixa->codigo,
                    'estado_nome' => $baixa_farmacia->estadoBaixa->nome,
                    // 'estado_texto' => $baixa_farmacia->estado_texto,
                    'data_baixa' => empty($baixa_farmacia->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->created_at)),
                    // 'data_criacao_pedido_aprovacao' => empty($baixa_farmacia->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_criacao_pedido_aprovacao)),
                    // 'data_aprovacao_pedido_aprovacao' => empty($baixa_farmacia->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_aprovacao_pedido_aprovacao)),
                    // 'resposavel_aprovacao_pedido_aprovacao' => $baixa_farmacia->resposavel_aprovacao_pedido_aprovacao,
                    // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                    'iten_ordem_reserva' => $itens_ordem_reserva,
                ];
            });

        $data = [
            'ordens_reserva' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Ordens de Reserva!', 200);
    }









    public function getOrdemReserva()
    {
        if (Gate::denies('gerir ordem reserva')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

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
            ->get()
            ->map(function ($baixa_farmacia) {

                $itens_ordem_reserva = [];

                foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                    // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                    $iten_ordem_reserva = [
                        'id' => $iten_baixa_farmacia->id,
                        'marca_medicamento_id' => $iten_baixa_farmacia->marca_medicamento_id,
                        'marca' => !empty($iten_baixa_farmacia->marcaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->marca : '',
                        'marca_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->codigo : '',
                        'medicamento_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->codigo : '',
                        'medicamento_pais_origem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->pais_origem : '',
                        'medicamento_nome_generico' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico->nome : '',
                        'medicamento_forma' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma : '',
                        'medicamento_dosagem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->dosagem : '',
                        'quantidade' => $iten_baixa_farmacia->quantidade,
                        'preco' => $iten_baixa_farmacia->preco,
                        'iva' => $iten_baixa_farmacia->iva,
                        'preco_iva' => $iten_baixa_farmacia->preco_iva,
                    ];
                    array_push($itens_ordem_reserva, $iten_ordem_reserva);
                    $iten_ordem_reserva = [];
                }


                $responsavel = $baixa_farmacia->responsavel;
                $comentario_baixa = $baixa_farmacia->comentario_baixa;

                if (is_array($responsavel))
                    usort($responsavel, sort_desc_array_objects('data'));

                if (is_array($comentario_baixa))
                    usort($comentario_baixa, sort_desc_array_objects('data'));

                return [
                    'id' => $baixa_farmacia->id,
                    'proveniencia' => $baixa_farmacia->proveniencia,
                    'empresa_id' => $baixa_farmacia->empresa_id,
                    'beneficiario_id' => $baixa_farmacia->beneficiario->id,
                    'nome_beneficiario' => $baixa_farmacia->beneficiario->nome,
                    'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                    'valor_baixa' => $baixa_farmacia->valor,
                    'nr_comprovativo' => $baixa_farmacia->nr_comprovativo,
                    'comprovativo' => $baixa_farmacia->comprovativo,
                    'responsavel' => $responsavel,
                    'comentario_baixa' => $comentario_baixa,
                    // 'estado' => $baixa_farmacia->estado,
                    // 'estado_id' => $baixa_farmacia->estadoBaixa->id,
                    'estado_codigo' => $baixa_farmacia->estadoBaixa->codigo,
                    'estado_nome' => $baixa_farmacia->estadoBaixa->nome,
                    // 'estado_texto' => $baixa_farmacia->estado_texto,
                    'data_baixa' => empty($baixa_farmacia->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->created_at)),
                    'updated_at' => empty($baixa_farmacia->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->updated_at)),
                    // 'data_criacao_pedido_aprovacao' => empty($baixa_farmacia->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_criacao_pedido_aprovacao)),
                    // 'data_aprovacao_pedido_aprovacao' => empty($baixa_farmacia->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_aprovacao_pedido_aprovacao)),
                    // 'resposavel_aprovacao_pedido_aprovacao' => $baixa_farmacia->resposavel_aprovacao_pedido_aprovacao,
                    // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                    'iten_ordem_reserva' => $itens_ordem_reserva,
                ];
            });

        $data = [
            'ordens_reserva' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Ordens de Reserva!', 200);
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
                    if (!delete_file_s3($path . $ficheiro)) {
                        return false;
                    }
                }
            }
        } else {
            if (!empty($ficheiros)) {
                if (!delete_file_s3($path . $ficheiros)) {
                    return false;
                }
            }
        }

        return true;
    }


    /**
     * Download a file of the specied Baixa
     * GET baixas/{proveniencia}/{id}/comprovativo/download/{ficheiro}
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

        $farmacia_id = request()->farmacia_id;

        $baixa_farmacia = $this->baixa_farmacia->byFarmacia($farmacia_id)->with('farmacia')->find($baixa_id);
        if (empty($baixa_farmacia)) {
            return $this->sendError('Baixa não encontrada!', 404);
        }

        // $path = kebab_case($baixa_farmacia->farmacia->nome) . '-' . $baixa_farmacia->farmacia->tenant_id . '/baixas/' . $baixa_farmacia->id . '/' . $ficheiro;
        $path = storage_path_farmacia($baixa_farmacia->farmacia->nome, $baixa_farmacia->farmacia->id, 'baixas') . "$baixa_farmacia->id/$ficheiro";
        return download_file_s3($path);
    }

    protected function abateItenStock($farmacia_id, $marca_medicamento_id, $quantidade_solicitada)
    {
        $retorno = [];

        $stock_farmacia = $this->stock_farmacia
            ->where([['farmacia_id', '=', $farmacia_id], ['marca_medicamento_id', '=', $marca_medicamento_id]])
            ->first();


        if (empty($stock_farmacia)) {
            $retorno['encontrado'] = false;
            return $retorno;
        }

        $quantidade_disponivel = $stock_farmacia->quantidade_disponivel;
        $quantidade_final = (int)$quantidade_disponivel - (int)$quantidade_solicitada;

        if ((int)$quantidade_solicitada > (int)$quantidade_disponivel) {
            $retorno['quantidade'] = false;
            return $retorno;
        }

        $stock_farmacia->quantidade_disponivel = $quantidade_final;
        $stock_farmacia->save();

        return $stock_farmacia;
    }

    protected function reporItenStock($farmacia_id, $marca_medicamento_id, $quantidade_solicitada)
    {
        $iten_satock_farmacia = $this->stock_farmacia
            ->where([['farmacia_id', '=', $farmacia_id], ['marca_medicamento_id', '=', $marca_medicamento_id]])
            ->first();

        if(empty($iten_satock_farmacia)) {
            return;
        }

        $quantidade_disponivel = $iten_satock_farmacia->quantidade_disponivel;
        $quantidade_final = (int)$quantidade_disponivel + (int)$quantidade_solicitada;
        $iten_satock_farmacia->quantidade_disponivel = $quantidade_final;
        $iten_satock_farmacia->save();

        return $iten_satock_farmacia;
    }
}
