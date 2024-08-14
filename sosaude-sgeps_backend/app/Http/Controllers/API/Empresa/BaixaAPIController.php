<?php

namespace App\Http\Controllers\API\Empresa;

use Exception;
use Carbon\Carbon;
use App\Models\EstadoBaixa;
use Illuminate\Http\Request;
use App\Models\BaixaFarmacia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\BaixaUnidadeSanitaria;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AppBaseController;
use App\Models\StockFarmacia;
use DateTime;

class BaixaAPIController extends AppBaseController
{
    private $baixa_farmacia;
    private $baixa_unidade_sanitaria;
    /**
     * Create a new BaixaAPIthController instance.
     *
     * @return void
     */
    public function __construct(BaixaFarmacia $baixa_farmacia, BaixaUnidadeSanitaria $baixa_unidade_sanitaria)
    {
        // $this->middleware(["CheckRole:1"]);
        $this->middleware(['check.estado'])->only(['confirmarBaixa', 'processarPagamentoBaixa', 'aprovarPedidoAprovacao', 'rejeitarPedidoAprovacao', 'devolverBaixa']);
        $this->baixa_farmacia = $baixa_farmacia;
        $this->baixa_unidade_sanitaria = $baixa_unidade_sanitaria;
    }

    /**
     * Display a listing of the BaixaFarmacia.
     * GET|HEAD /empresa/baixas
     *
     * @return Response
     */
    public function indexBaixa()
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        }
        //$resumo = ["aguarda_confirmacao" => 0, "aguarda_pagamento" => 0];
        $confirmacao_valor_total = 0;
        $pagamento_valor_total = 0;
        $aguarda_confirmacao = 0;
        $aguarda_pagamento = 0;
        $baixas_farmacia = $this->baixa_farmacia
            ->byEmpresa($empresa_id)
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
                    'proveniencia' => $baixa_farmacia->proveniencia,
                    'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                    'nome_beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                    'nome_dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                    'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                    'id_instituicao' => $baixa_farmacia->farmacia->id,
                    'valor_baixa' => $baixa_farmacia->valor,
                    'nr_comprovativo' => $baixa_farmacia->nr_comprovativo,
                    'comprovativo' => $baixa_farmacia->comprovativo,
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
                    'comentario_baixa' => $comentario_baixa,
                    'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                    'descricao' => $descricao,
                ];
            })->toArray();
        $nr_baixas_far = 0;
        foreach ($baixas_farmacia as $baixa) {
            if ($baixa['estado_id'] == 4) {
                $nr_baixas_far += 1;
                $aguarda_confirmacao += 1;
                $confirmacao_valor_total += $baixa['valor_baixa'];
            } elseif ($baixa['estado_id'] == 5) {
                $aguarda_pagamento += 1;
                $pagamento_valor_total += $baixa['valor_baixa'];
            }
        }

        /** @var BaixaUnidadeSanitaria $baixas_unidade_sanitaria*/
        $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
            ->byEmpresa($empresa_id)
            ->with(
                'unidadeSanitaria:id,nome',
                'estadoBaixa:id,nome,codigo',
                'beneficiario:id,nome',
                'dependenteBeneficiario:id,nome',
                'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,servico_id,baixa_unidade_sanitaria_id',
                'itensBaixaUnidadeSanitaria.servico:id,nome'
            )
            ->orderBy('updated_at', 'DESC')
            ->get(['id', 'valor', 'responsavel', 'proveniencia', 'comprovativo','nr_comprovativo', 'data_criacao_pedido_aprovacao', 'data_aprovacao_pedido_aprovacao', 'resposavel_aprovacao_pedido_aprovacao', 'comentario_baixa', 'comentario_pedido_aprovacao', 'unidade_sanitaria_id', 'estado_baixa_id', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'created_at', 'updated_at'])
            ->map(function ($baixa_unidade_sanitaria) {

                $descricao = [];
                foreach ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria as $iten_baixa_unidade_sanitaria) {
                    $descricao_actual = [
                        'servico' => $iten_baixa_unidade_sanitaria->servico->nome,
                        'quantidade' => $iten_baixa_unidade_sanitaria->quantidade,
                        'preco' => $iten_baixa_unidade_sanitaria->preco,
                        'iva' => $iten_baixa_unidade_sanitaria->iva,
                        'preco_iva' => $iten_baixa_unidade_sanitaria->preco_iva,
                    ];
                    array_push($descricao, $descricao_actual);
                    $descricao_actual = [];
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
                    'beneficio_proprio_beneficiario' => $baixa_unidade_sanitaria->beneficio_proprio_beneficiario,
                    'nome_beneficiario' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : null,
                    'nome_dependente' => !empty($baixa_unidade_sanitaria->dependenteBeneficiario) ? $baixa_unidade_sanitaria->dependenteBeneficiario->nome : null,
                    'nome_instituicao' => $baixa_unidade_sanitaria->unidadeSanitaria->nome,
                    'id_instituicao' => $baixa_unidade_sanitaria->unidadeSanitaria->id,
                    'valor_baixa' => $baixa_unidade_sanitaria->valor,
                    // 'estado' => $baixa_unidade_sanitaria->estado,
                    'estado_id' => $baixa_unidade_sanitaria->estadoBaixa->id,
                    'estado_codigo' => $baixa_unidade_sanitaria->estadoBaixa->codigo,
                    'estado_nome' => $baixa_unidade_sanitaria->estadoBaixa->nome,
                    'nr_comprovativo' => $baixa_unidade_sanitaria->nr_comprovativo,
                    'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                    // 'estado_texto' => $baixa_unidade_sanitaria->estado_texto,
                    'data_baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                    'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                    'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                    'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                    'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                    'responsavel' => $responsavel,
                    'comentario_baixa' => $comentario_baixa,
                    'comentario_pedido_aprovacao' => $baixa_unidade_sanitaria->comentario_pedido_aprovacao,
                    'descricao' => $descricao,
                ];
            })->toArray();
        //return response()->json($baixas_unidade_sanitaria, 200);
        //dd($baixas_unidade_sanitaria);

        $nr_baixas_us = 0;
        foreach ($baixas_unidade_sanitaria as $baixa) {
            if ($baixa['estado_id'] == 4) {
                $nr_baixas_us += 1;
                $aguarda_confirmacao += 1;
                $confirmacao_valor_total += $baixa['valor_baixa'];
            } elseif ($baixa['estado_id'] == 5) {
                $aguarda_pagamento += 1;
                $pagamento_valor_total += $baixa['valor_baixa'];
            }
        }



        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);





        $meses = [];
        $meses_cont = [];
        foreach ($baixas as $baixa_item) {
            if ($baixa_item['estado_id'] == 4) {
                // $baixa_mes = Carbon::parse($baixa_item['data_baixa'])->format('Y-m-d');
                $baixa_mes = intval(Carbon::parse($baixa_item['data_baixa'])->format('m'));
                $data = DateTime::createFromFormat("!m", $baixa_mes)->format("F");

                if(in_array($data, $meses)){
                    $key = array_search($data, $meses);
                    $meses_cont[$key] = $meses_cont[$key] + 1;
                }
                else{
                    array_push($meses, $data);
                    array_push($meses_cont, 1);
                }
            }
        }



        $months = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July ',
            'August',
            'September',
            'October',
            'November',
            'December',
        );

        
        $monthst = array(
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro',
        );
        $meses_cont2 = [0,0,0,0,0,0,0,0,0,0,0,0];
       
        foreach ($baixas as $baixa_item) {
            if ($baixa_item['estado_id'] == 4) {
                // $baixa_mes = Carbon::parse($baixa_item['data_baixa'])->format('Y-m-d');
                $baixa_mes = intval(Carbon::parse($baixa_item['data_baixa'])->format('m'));
                $data = DateTime::createFromFormat("!m", $baixa_mes)->format("F");
                $us_name = $baixa_item['nome_instituicao'];
                $us_id = $baixa_item['id_instituicao'];

                if(in_array($data, $months)){
                    $key = array_search($data, $months);
                    $meses_cont2[$key] = $meses_cont2[$key] + 1;
                }
                
            }
        }

        
        $meses_nomes = array_map(function ($item) use ($meses){
            if($item == 'January'){
                return "Janeiro";
            }
            elseif($item == 'February'){
                return "Fevereiro";
            }
            elseif($item == 'March'){
                return "Março";
            }
            elseif($item == 'April'){
                return "Abril";
            }
            elseif($item == 'May'){
                return "Maio";
            }
            elseif($item == 'June'){
                return "Junho";
            }
            elseif($item == 'July'){
                return "Julho";
            }
            elseif($item == 'August'){
                return "Agosto";
            }
            elseif($item == 'September'){
                return "Setembro";
            }
            elseif($item == 'October'){
                return "Outubro";
            }
            elseif($item == 'November'){
                return "Novembro";
            }
            elseif($item == 'December'){
                return "Dezembro";
            }
        }, $meses);


        // $date = 12;
        // $data = DateTime::createFromFormat("!m", $date);

        // dd($data->format("F"));
        // dd([$meses, $meses_cont]);

        // $nr_baixas_us = count($baixas) - count($baixas_farmacia);
        // $nr_baixas_far =count($baixas_farmacia);
        $data = [
            'baixas' => $baixas,
            'resumo' => [$aguarda_confirmacao, $aguarda_pagamento],
            'valor_total' => [round($confirmacao_valor_total, 2), round($pagamento_valor_total, 2)],
            'total_baixas' => [$nr_baixas_far, $nr_baixas_us],
            'meses' => $meses_nomes,
            'meses_baixas' => $meses_cont,
            'meses2' => $monthst,
            'meses_baixas2' => $meses_cont2
        ];

        return $this->sendResponse($data, 'Baixas Farmacia retrieved successfully!', 200);
    }
    public function indexBaixaExcel()
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        }
        //$resumo = ["aguarda_confirmacao" => 0, "aguarda_pagamento" => 0];
        $confirmacao_valor_total = 0;
        $pagamento_valor_total = 0;
        $aguarda_confirmacao = 0;
        $aguarda_pagamento = 0;
        $baixas_farmacia = $this->baixa_farmacia
            ->byEmpresa($empresa_id)
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
                    // 'proveniencia' => $baixa_farmacia->proveniencia,
                    // 'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                    'Nome Beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                    'Nome Dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                    'Instituição' => $baixa_farmacia->farmacia->nome,
                    'Valor Baixa' => $baixa_farmacia->valor,
                    'Nº comprovativo' => $baixa_farmacia->nr_comprovativo,
                    // 'comprovativo' => $baixa_farmacia->comprovativo,
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
                    // 'comentario_baixa' => $comentario_baixa,
                    // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                    // 'descricao' => $descricao,
                ];
            })->toArray();
        $nr_baixas_far = 0;


        /** @var BaixaUnidadeSanitaria $baixas_unidade_sanitaria*/
        $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
            ->byEmpresa($empresa_id)
            ->with(
                'unidadeSanitaria:id,nome',
                'estadoBaixa:id,nome,codigo',
                'beneficiario:id,nome',
                'dependenteBeneficiario:id,nome',
                'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,servico_id,baixa_unidade_sanitaria_id',
                'itensBaixaUnidadeSanitaria.servico:id,nome'
            )
            ->orderBy('updated_at', 'DESC')
            ->get(['id', 'valor', 'responsavel', 'proveniencia', 'comprovativo', 'data_criacao_pedido_aprovacao', 'data_aprovacao_pedido_aprovacao', 'resposavel_aprovacao_pedido_aprovacao', 'comentario_baixa', 'comentario_pedido_aprovacao', 'unidade_sanitaria_id', 'estado_baixa_id', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'created_at', 'updated_at'])
            ->map(function ($baixa_unidade_sanitaria) {

                $descricao = [];
                foreach ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria as $iten_baixa_unidade_sanitaria) {
                    $descricao_actual = [
                        'servico' => $iten_baixa_unidade_sanitaria->servico->nome,
                        'quantidade' => $iten_baixa_unidade_sanitaria->quantidade,
                        'preco' => $iten_baixa_unidade_sanitaria->preco,
                        'iva' => $iten_baixa_unidade_sanitaria->iva,
                        'preco_iva' => $iten_baixa_unidade_sanitaria->preco_iva,
                    ];
                    array_push($descricao, $descricao_actual);
                    $descricao_actual = [];
                }


                $responsavel = $baixa_unidade_sanitaria->responsavel;
                $comentario_baixa = $baixa_unidade_sanitaria->comentario_baixa;

                if (is_array($responsavel))
                    usort($responsavel, sort_desc_array_objects('data'));

                if (is_array($comentario_baixa))
                    usort($comentario_baixa, sort_desc_array_objects('data'));

                return [
                    // 'id' => $baixa_unidade_sanitaria->id,
                    // 'proveniencia' => $baixa_unidade_sanitaria->proveniencia,
                    // 'beneficio_proprio_beneficiario' => $baixa_unidade_sanitaria->beneficio_proprio_beneficiario,
                    'Beneficiario' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : null,
                    'Dependente' => !empty($baixa_unidade_sanitaria->dependenteBeneficiario) ? $baixa_unidade_sanitaria->dependenteBeneficiario->nome : null,
                    'Instiuição' => $baixa_unidade_sanitaria->unidadeSanitaria->nome,
                    'Valor baixa' => $baixa_unidade_sanitaria->valor,
                    // 'estado' => $baixa_unidade_sanitaria->estado,
                    // 'estado_id' => $baixa_unidade_sanitaria->estadoBaixa->id,
                    // 'estado_codigo' => $baixa_unidade_sanitaria->estadoBaixa->codigo,
                    'Estado' => $baixa_unidade_sanitaria->estadoBaixa->nome,
                    'Nº comprovativo' => $baixa_unidade_sanitaria->nr_comprovativo,
                    // 'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                    // 'estado_texto' => $baixa_unidade_sanitaria->estado_texto,
                    'Data baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                    // 'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                    // 'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                    // 'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                    // 'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                    // 'responsavel' => $responsavel,
                    // 'comentario_baixa' => $comentario_baixa,
                    // 'comentario_pedido_aprovacao' => $baixa_unidade_sanitaria->comentario_pedido_aprovacao,
                    // 'descricao' => $descricao,
                ];
            })->toArray();
        //return response()->json($baixas_unidade_sanitaria, 200);
        //dd($baixas_unidade_sanitaria);




        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);



        // $date = 12;
        // $data = DateTime::createFromFormat("!m", $date);

        // dd($data->format("F"));
        // dd([$meses, $meses_cont]);

        // $nr_baixas_us = count($baixas) - count($baixas_farmacia);
        // $nr_baixas_far =count($baixas_farmacia);
        $data = [
            'baixas' => $baixas,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia retrieved successfully!', 200);
    }

    /**
     * Set the estado value to 2(Aguardando Pagameto) of the specified Baixa from the request comming from middleware, in the storage
     * POST empresa/confirmar_baixa
     * @param Request $request
     * @return Response
     */

    public function confirmarBaixa(Request $request)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // dd($request->all());
        $request->validate(
            [
                'ficheiros' => 'nullable|array',
                'ficheiros.*' => 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            ]
        );
        // $input = $request->only(['proveniencia']);
        $proveniencia = $request->proveniencia;
        $temp_nome_ficheiros = [];
        $estado_baixa = EstadoBaixa::where('codigo', '11')->first();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $input['estado_baixa_id'] = $estado_baixa->id;
        $objecto_responsavel_array = [
            'nome' => $utilizador_empresa->nome,
            'accao' => 'Confirmou a Baixa',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];

        if (!empty($request->ficheiros)) {
            if (!validate_files($request, 'ficheiros')) {
                $errors['ficheiros'] = 'Algum dos ficheiros encontrados foi identificado como inválido!';
                return $this->sendErrorValidation($errors, 422);
            }
        }

        if (empty($estado_baixa)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }


        if (!empty($request->baixa->responsavel)) {
            $responsavel = $request->baixa->responsavel;
            if (is_array($responsavel)) {
                array_push($responsavel, $objecto_responsavel_array);
            } else {
                $responsavel = array();
                array_push($responsavel, $objecto_responsavel_array);
            }

            $input['responsavel'] = $responsavel;
        } else {
            $input['responsavel'] = [$objecto_responsavel_array];
        }

        DB::beginTransaction();
        try {

            if ($proveniencia == 1) {

                $baixa_farmacia = $request->baixa;

                // $path = kebab_case($baixa_farmacia->farmacia->nome) . '-' . $baixa_farmacia->farmacia->tenant_id . '/baixas/' . $baixa_farmacia->id . '/';
                $path = storage_path_farmacia($baixa_farmacia->farmacia->nome, $baixa_farmacia->farmacia->id, 'baixas') . "$baixa_farmacia->id/";

                if (!empty($request->ficheiros)) {
                    foreach ($request->ficheiros as $ficheiro) {

                        $upload = upload_file_s3($path, $ficheiro);

                        if (empty($upload)) {
                            DB::rollback();
                            if (!empty($path)) {
                                if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                                    return $this->sendError('Não foram removidos todos ficheiros!', 500);
                                }
                            }
                            return $this->sendError('O não foi possível efectuar o carregamento do(s) arquivo(s)!', 500);
                        } else {
                            array_push($temp_nome_ficheiros, $upload);
                        }
                    }
                }

                $baixa_farmacia->comprovativo = isset($baixa_farmacia->comprovativo) ? array_merge($baixa_farmacia->comprovativo, $temp_nome_ficheiros) : $temp_nome_ficheiros;
                // $this->actualizarEstadoBaixa($baixa_farmacia, 2);
                $this->actualizarEstadoBaixa($baixa_farmacia, $input);

                DB::commit();
                $temp_nome_ficheiros = [];
            } elseif ($proveniencia == 2) {

                $baixa_unidade_sanitaria = $request->baixa;

                // $path = kebab_case($baixa_unidade_sanitaria->unidadeSanitaria->nome) . '-' . $baixa_unidade_sanitaria->unidadeSanitaria->tenant_id . '/baixas/' . $baixa_unidade_sanitaria->id . '/';
                $path = storage_path_u_sanitaria($baixa_unidade_sanitaria->unidadeSanitaria->nome, $baixa_unidade_sanitaria->unidadeSanitaria->id, 'baixas') . "$baixa_unidade_sanitaria->id/";

                if (!empty($request->ficheiros)) {
                    foreach ($request->ficheiros as $ficheiro) {

                        $upload = upload_file_s3($path, $ficheiro);

                        if (empty($upload)) {
                            DB::rollback();
                            if (!empty($path)) {
                                if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                                    return $this->sendError('Não foram removidos todos ficheiros!', 500);
                                }
                            }
                            return $this->sendError('O não foi possível efectuar o carregamento do(s) arquivo(s)!', 500);
                        } else {
                            array_push($temp_nome_ficheiros, $upload);
                        }
                    }
                }

                $baixa_unidade_sanitaria->comprovativo = isset($baixa_unidade_sanitaria->comprovativo) ? array_merge($baixa_unidade_sanitaria->comprovativo, $temp_nome_ficheiros) : $temp_nome_ficheiros;
                $this->actualizarEstadoBaixa($baixa_unidade_sanitaria, $input);

                DB::commit();
                $temp_nome_ficheiros = [];
            } else {
                DB::rollback();
                return $this->sendError('Provêninecia Desconhecida', 404);
            }

            return $this->sendSuccess('Baixa actualizada com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            if (!empty($path)) {
                if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                    return $this->sendError('Não foram removidos todos ficheiros! ' . $e->getMessage(), 500);
                }
            }
            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function confirmarBaixaBulk(Request $request)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate([
            'baixas' => 'required|array',
            'baixas.*.proveniencia' => [
                'required',
                Rule::in([1, 2])
            ]
        ]);
        // $input = $request->only(['proveniencia']);
        $input_baixas = $request->baixas;
        $empresa_id = $request->empresa_id;
        $estado_baixa = EstadoBaixa::where('codigo', '11')->first();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $objecto_responsavel_array = [
            'nome' => $utilizador_empresa->nome,
            'accao' => 'Confirmou a Baixa',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];

        if (empty($estado_baixa)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        DB::beginTransaction();
        try {
            foreach ($input_baixas as $input_baixa) {
                if ($input_baixa['proveniencia'] == 1) {
                    $baixa_farmacia = $this->baixa_farmacia->byEmpresa($empresa_id)->find($input_baixa['id']);
                    if (empty($baixa_farmacia)) {
                        DB::rollback();
                        return $this->sendError('Baixa não encontrada!', 404);
                    }
                    $input = array();
                    $input['estado_baixa_id'] = $estado_baixa->id;

                    if (!empty($baixa_farmacia->responsavel)) {
                        $responsavel = $baixa_farmacia->responsavel;
                        if (is_array($responsavel)) {
                            array_push($responsavel, $objecto_responsavel_array);
                        } else {
                            $responsavel = array();
                            array_push($responsavel, $objecto_responsavel_array);
                        }

                        $input['responsavel'] = $responsavel;
                    } else {
                        $input['responsavel'] = [$objecto_responsavel_array];
                    }

                    $this->actualizarEstadoBaixa($baixa_farmacia, $input);
                } elseif ($input_baixa['proveniencia'] == 2) {

                    $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria->byEmpresa($empresa_id)->find($input_baixa['id']);
                    if (empty($baixa_unidade_sanitaria)) {
                        DB::rollback();
                        return $this->sendError('Baixa não encontrada!', 404);
                    }
                    $input = array();
                    $input['estado_baixa_id'] = $estado_baixa->id;

                    if (!empty($baixa_unidade_sanitaria->responsavel)) {
                        $responsavel = $baixa_unidade_sanitaria->responsavel;
                        if (is_array($responsavel)) {
                            array_push($responsavel, $objecto_responsavel_array);
                        } else {
                            $responsavel = array();
                            array_push($responsavel, $objecto_responsavel_array);
                        }

                        $input['responsavel'] = $responsavel;
                    } else {
                        $input['responsavel'] = [$objecto_responsavel_array];
                    }

                    $this->actualizarEstadoBaixa($baixa_unidade_sanitaria, $input);
                }
            }

            DB::commit();
            return $this->sendSuccess('Baixas actualizadas com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError('Erro ao actualizar as Baixas', 404);
        }
    }

    protected function confirmarBaixaUnicaFarmacia($id)
    {
    }

    /**
     * Set estado value to 3(Pagamento Processado) of the specified Baixa from the request comming from middleware, in the storage
     * POST empresa/processar_pagamento
     * @param Request $request
     * @return Response
     */
    public function processarPagamentoBaixa(Request $request)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate(['proveniencia' => 'required|integer']);
        // $input = $request->only(['proveniencia']);
        $proveniencia = $request->proveniencia;
        $estado_baixa = EstadoBaixa::where('codigo', '12')->first();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $input['estado_baixa_id'] = $estado_baixa->id;
        $objecto_responsavel_array = [
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'accao' => 'Processou o Pagamento da Baixa',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];

        if (empty($estado_baixa)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        if (!empty($request->baixa->responsavel)) {
            $responsavel = $request->baixa->responsavel;
            if (is_array($responsavel)) {
                array_push($responsavel, $objecto_responsavel_array);
            } else {
                $responsavel = array();
                array_push($responsavel, $objecto_responsavel_array);
            }

            $input['responsavel'] = $responsavel;
        } else {
            $input['responsavel'] = [$objecto_responsavel_array];
        }

        DB::beginTransaction();
        try {

            if ($proveniencia == 1) {

                $baixa_farmacia = $request->baixa;

                $this->actualizarEstadoBaixa($baixa_farmacia, $input);
                DB::commit();
            } elseif ($proveniencia == 2) {

                $baixa_unidade_sanitaria = $request->baixa;

                $this->actualizarEstadoBaixa($baixa_unidade_sanitaria, $input);
                DB::commit();
            } else {
                DB::rollback();
                return $this->sendError('Provêninecia Desconhecida', 404);
            }

            return $this->sendSuccess('Baixa actualizada com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }


    public function processarPagamentoBaixaBulk(Request $request)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate([
            'baixas' => 'required|array',
            'baixas.*.proveniencia' => [
                'required',
                Rule::in([1, 2])
            ]
        ]);

        $input_baixas = $request->baixas;
        $empresa_id = $request->empresa_id;
        $estado_baixa = EstadoBaixa::where('codigo', '12')->first();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $objecto_responsavel_array = [
            'nome' => $utilizador_empresa->nome,
            'accao' => 'Processou o Pagamento da Baixa',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];

        if (empty($estado_baixa)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        DB::beginTransaction();
        try {
            foreach ($input_baixas as $input_baixa) {
                if ($input_baixa['proveniencia'] == 1) {
                    $baixa_farmacia = $this->baixa_farmacia->byEmpresa($empresa_id)->find($input_baixa['id']);
                    if (empty($baixa_farmacia)) {
                        DB::rollback();
                        return $this->sendError('Baixa não encontrada!', 404);
                    }
                    $input = array();
                    $input['estado_baixa_id'] = $estado_baixa->id;

                    if (!empty($baixa_farmacia->responsavel)) {
                        $responsavel = $baixa_farmacia->responsavel;
                        if (is_array($responsavel)) {
                            array_push($responsavel, $objecto_responsavel_array);
                        } else {
                            $responsavel = array();
                            array_push($responsavel, $objecto_responsavel_array);
                        }

                        $input['responsavel'] = $responsavel;
                    } else {
                        $input['responsavel'] = [$objecto_responsavel_array];
                    }

                    $this->actualizarEstadoBaixa($baixa_farmacia, $input);
                } elseif ($input_baixa['proveniencia'] == 2) {

                    $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria->byEmpresa($empresa_id)->find($input_baixa['id']);
                    if (empty($baixa_unidade_sanitaria)) {
                        DB::rollback();
                        return $this->sendError('Baixa não encontrada!', 404);
                    }
                    $input = array();
                    $input['estado_baixa_id'] = $estado_baixa->id;

                    if (!empty($baixa_unidade_sanitaria->responsavel)) {
                        $responsavel = $baixa_unidade_sanitaria->responsavel;
                        if (is_array($responsavel)) {
                            array_push($responsavel, $objecto_responsavel_array);
                        } else {
                            $responsavel = array();
                            array_push($responsavel, $objecto_responsavel_array);
                        }

                        $input['responsavel'] = $responsavel;
                    } else {
                        $input['responsavel'] = [$objecto_responsavel_array];
                    }

                    $this->actualizarEstadoBaixa($baixa_unidade_sanitaria, $input);
                }
            }

            DB::commit();
            return $this->sendSuccess('Baixas actualizadas com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError('Erro ao actualizar as Baixas', 404);
        }
    }




    public function devolverBaixa(Request $request)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $user = Auth::user();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;

        $request->validate(['proveniencia' => 'required|integer', 'comentario_baixa' => 'nullable|string|max:255']);
        // $input = $request->only(['proveniencia']);

        $objecto_comentario_array = [
            'proveniencia' => 'Empresa',
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'data' => date('d-m-Y H:i:s', strtotime(now())),
            'comentario' => $request->comentario_baixa
        ];
        $objecto_responsavel_array = [
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'accao' => 'Devolveu a Baixa',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];

        $proveniencia = $request->proveniencia;
        $estado_baixa = EstadoBaixa::where('codigo', '13')->first();
        $input['estado_baixa_id'] = $estado_baixa->id;
        $input['comentario_baixa'] = [];

        if (!empty($request->comentario_baixa)) {
            $comentario = $request->baixa->comentario_baixa;
            if (is_array($comentario)) {
                array_push($comentario, $objecto_comentario_array);
            } else {
                $comentario = array();
                array_push($comentario, $objecto_comentario_array);
            }

            $input['comentario_baixa'] = $comentario;
        }


        if (!empty($request->baixa->responsavel)) {
            $responsavel = $request->baixa->responsavel;
            if (is_array($responsavel)) {
                array_push($responsavel, $objecto_responsavel_array);
            } else {
                $responsavel = array();
                array_push($responsavel, $objecto_responsavel_array);
            }

            $input['responsavel'] = $responsavel;
        } else {
            $input['responsavel'] = [$objecto_responsavel_array];
        }

        if (empty($estado_baixa)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        DB::beginTransaction();
        try {

            if ($proveniencia == 1) {

                $baixa_farmacia = $request->baixa;

                $this->actualizarEstadoBaixa($baixa_farmacia, $input);

                // if($baixa_farmacia->itensBaixaFarmacia->isNotEmpty()) {
                //     foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa) {
                //         $repor_stcok_farmacia = $this->reporItenStock($baixa_farmacia->farmacia_id, $iten_baixa->marca_medicamento_id, $iten_baixa->quantidade);
                //     }
                // }


                DB::commit();
            } elseif ($proveniencia == 2) {

                $baixa_unidade_sanitaria = $request->baixa;

                $this->actualizarEstadoBaixa($baixa_unidade_sanitaria, $input);
                DB::commit();
            } else {
                DB::rollback();
                return $this->sendError('Provêninecia Desconhecida', 404);
            }

            return $this->sendSuccess('Baixa actualizada com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }


    public function indexPedidoAprovacao()
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        }
        $confirmacao_valor_total = 0;
        $pagamento_valor_total = 0;
        $aguarda_confirmacao = 0;
        $aguarda_pagamento = 0;

        $estado_pedido_rejeiado = EstadoBaixa::where('codigo', '7')->first();
        $estado_pedido_aguarda_aprovacao = EstadoBaixa::where('codigo', '8')->first();
        $estado_pedido_aguarda_inicializacao = EstadoBaixa::where('codigo', '9')->first();
        // 
        if (empty($estado_pedido_rejeiado))
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);

        if (empty($estado_pedido_aguarda_aprovacao))
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);

        if (empty($estado_pedido_aguarda_inicializacao))
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);

        $empresa_id = request()->empresa_id;

        // dd($empresa_id);
        $baixas_farmacia = $this->baixa_farmacia->byEmpresa($empresa_id)
            // $baixas_farmacia = $this->baixa_farmacia->byEmpresa($empresa_id)
            ->where(function ($query) use ($estado_pedido_rejeiado, $estado_pedido_aguarda_aprovacao, $estado_pedido_aguarda_inicializacao) {
                $query->where('estado_baixa_id', $estado_pedido_rejeiado->id);
                $query->orWhere('estado_baixa_id', $estado_pedido_aguarda_aprovacao->id);
                $query->orWhere('estado_baixa_id', $estado_pedido_aguarda_inicializacao->id);
            })
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
                    'proveniencia' => $baixa_farmacia->proveniencia,
                    'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                    'nome_beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                    'nome_dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                    'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                    'valor_baixa' => $baixa_farmacia->valor,
                    'nr_comprovativo' => $baixa_farmacia->nr_comprovativo,
                    'comprovativo' => $baixa_farmacia->comprovativo,
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
                    'comentario_baixa' => $comentario_baixa,
                    'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                    'descricao' => $descricao,
                ];
            })->toArray();

        $nr_baixas_far = 0;

        foreach ($baixas_farmacia as $baixa) {
            if ($baixa['estado_id'] == 2) {
                $nr_baixas_far += 1;
                $aguarda_confirmacao += 1;
                $confirmacao_valor_total += $baixa['valor_baixa'];
            } elseif ($baixa['estado_id'] == 3) {
                $aguarda_pagamento += 1;
                $pagamento_valor_total += $baixa['valor_baixa'];
            }
        }

        /** @var BaixaUnidadeSanitaria $baixas_unidade_sanitaria*/
        $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
            ->byEmpresa($empresa_id)
            ->where(function ($query) use ($estado_pedido_rejeiado, $estado_pedido_aguarda_aprovacao, $estado_pedido_aguarda_inicializacao) {
                $query->where('estado_baixa_id', $estado_pedido_rejeiado->id);
                $query->orWhere('estado_baixa_id', $estado_pedido_aguarda_aprovacao->id);
                $query->orWhere('estado_baixa_id', $estado_pedido_aguarda_inicializacao->id);
            })
            //->where('estado_baixa_id', $estado_pedido_rejeiado->id)
            //->orWhere('estado_baixa_id', $estado_pedido_aguarda_aprovacao->id)
            //->orWhere('estado_baixa_id', $estado_pedido_aguarda_inicializacao->id)
            ->with(
                'beneficiario:id,nome',
                'dependenteBeneficiario:id,nome',
                'unidadeSanitaria:id,nome',
                'estadoBaixa:id,nome,codigo',
                'beneficiario:id,nome',
                'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,servico_id,baixa_unidade_sanitaria_id',
                'itensBaixaUnidadeSanitaria.servico:id,nome'
            )
            ->get(['id', 'valor', 'responsavel', 'proveniencia', 'comprovativo', 'data_criacao_pedido_aprovacao', 'data_aprovacao_pedido_aprovacao', 'resposavel_aprovacao_pedido_aprovacao', 'comentario_baixa', 'comentario_pedido_aprovacao', 'unidade_sanitaria_id', 'estado_baixa_id', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'created_at', 'updated_at'])
            ->map(function ($baixa_unidade_sanitaria) {

                $descricao = [];
                foreach ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria as $iten_baixa_unidade_sanitaria) {
                    $descricao_actual = [
                        'servico' => $iten_baixa_unidade_sanitaria->servico->nome,
                        'quantidade' => $iten_baixa_unidade_sanitaria->quantidade,
                        'preco' => $iten_baixa_unidade_sanitaria->preco,
                        'iva' => $iten_baixa_unidade_sanitaria->iva,
                        'preco_iva' => $iten_baixa_unidade_sanitaria->preco_iva,
                    ];
                    array_push($descricao, $descricao_actual);
                    $descricao_actual = [];
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
                    'beneficio_proprio_beneficiario' => $baixa_unidade_sanitaria->beneficio_proprio_beneficiario,
                    'nome_beneficiario' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : null,
                    'nome_dependente' => !empty($baixa_unidade_sanitaria->dependenteBeneficiario) ? $baixa_unidade_sanitaria->dependenteBeneficiario->nome : null,
                    'nome_instituicao' => $baixa_unidade_sanitaria->unidadeSanitaria->nome,
                    'valor_baixa' => $baixa_unidade_sanitaria->valor,
                    // 'estado' => $baixa_unidade_sanitaria->estado,
                    'estado_id' => $baixa_unidade_sanitaria->estadoBaixa->id,
                    'estado_codigo' => $baixa_unidade_sanitaria->estadoBaixa->codigo,
                    'estado_nome' => $baixa_unidade_sanitaria->estadoBaixa->nome,
                    'nr_comprovativo' => $baixa_unidade_sanitaria->nr_comprovativo,
                    'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                    // 'estado_texto' => $baixa_unidade_sanitaria->estado_texto,
                    'data_baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                    'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                    'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                    'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                    'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                    'responsavel' => $responsavel,
                    'comentario_baixa' => $comentario_baixa,
                    'comentario_pedido_aprovacao' => $baixa_unidade_sanitaria->comentario_pedido_aprovacao,
                    'descricao' => $descricao,
                ];
            })->toArray();
        // dd($baixas_unidade_sanitaria);

        $nr_baixas_us = 0;

        foreach ($baixas_unidade_sanitaria as $baixa) {
            if ($baixa['estado_id'] == 2) {
                $nr_baixas_us += 1;
                $aguarda_confirmacao += 1;
                $confirmacao_valor_total += $baixa['valor_baixa'];
            } elseif ($baixa['estado_id'] == 3) {
                $aguarda_pagamento += 1;
                $pagamento_valor_total += $baixa['valor_baixa'];
            }
        }

        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);

        // $nr_baixas_us = count($baixas) - count($baixas_farmacia);
        // $nr_baixas_far =count($baixas_farmacia);
        $meses = [];
        $meses_cont = [];
        foreach ($baixas as $baixa_item) {
            if ($baixa_item['estado_id'] == 2) {
                // $baixa_mes = Carbon::parse($baixa_item['data_baixa'])->format('Y-m-d');
                $baixa_mes = intval(Carbon::parse($baixa_item['data_baixa'])->format('m'));
                $data = DateTime::createFromFormat("!m", $baixa_mes)->format("F");

                if(in_array($data, $meses)){
                    $key = array_search($data, $meses);
                    $meses_cont[$key] = $meses_cont[$key] + 1;
                }
                else{
                    array_push($meses, $data);
                    array_push($meses_cont, 1);
                }
            }
        } 


        $months = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July ',
            'August',
            'September',
            'October',
            'November',
            'December',
        );

        
        $monthst = array(
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro',
        );
        $meses_cont2 = [0,0,0,0,0,0,0,0,0,0,0,0];
       
        foreach ($baixas as $baixa_item) {
            if ($baixa_item['estado_id'] == 4) {
                // $baixa_mes = Carbon::parse($baixa_item['data_baixa'])->format('Y-m-d');
                $baixa_mes = intval(Carbon::parse($baixa_item['data_baixa'])->format('m'));
                $data = DateTime::createFromFormat("!m", $baixa_mes)->format("F");
                $us_name = $baixa_item['nome_instituicao'];
                $us_id = $baixa_item['id_instituicao'];

                if(in_array($data, $months)){
                    $key = array_search($data, $months);
                    $meses_cont2[$key] = $meses_cont2[$key] + 1;
                }
                
            }
        }




        $meses_nomes = array_map(function ($item) use ($meses){
            if($item == 'January'){
                return "Janeiro";
            }
            elseif($item == 'February'){
                return "Fevereiro";
            }
            elseif($item == 'March'){
                return "Março";
            }
            elseif($item == 'April'){
                return "Abril";
            }
            elseif($item == 'May'){
                return "Maio";
            }
            elseif($item == 'June'){
                return "Junho";
            }
            elseif($item == 'July'){
                return "Julho";
            }
            elseif($item == 'August'){
                return "Agosto";
            }
            elseif($item == 'September'){
                return "Setembro";
            }
            elseif($item == 'October'){
                return "Outubro";
            }
            elseif($item == 'November'){
                return "Novembro";
            }
            elseif($item == 'December'){
                return "Dezembro";
            }
        }, $meses);

        // dd([$meses, $meses_cont]);

        $data = [
            'baixas' => $baixas,
            'resumo' => [$aguarda_confirmacao, $aguarda_pagamento],
            'valor_total' => [round($confirmacao_valor_total, 2), round($pagamento_valor_total, 2)],
            'total_baixas' => [$nr_baixas_far, $nr_baixas_us],
            'meses' => $meses_nomes,
            'meses_baixas' => $meses_cont,
            'meses2' => $monthst,
            'meses_baixas2' => $meses_cont2
        ];

        return $this->sendResponse($data, 'Baixas Farmacia retrieved successfully!', 200);
    }
    public function indexPedidoAprovacaoExcel()
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        }
        $confirmacao_valor_total = 0;
        $pagamento_valor_total = 0;
        $aguarda_confirmacao = 0;
        $aguarda_pagamento = 0;

        $estado_pedido_rejeiado = EstadoBaixa::where('codigo', '7')->first();
        $estado_pedido_aguarda_aprovacao = EstadoBaixa::where('codigo', '8')->first();
        $estado_pedido_aguarda_inicializacao = EstadoBaixa::where('codigo', '9')->first();
        // 
        if (empty($estado_pedido_rejeiado))
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);

        if (empty($estado_pedido_aguarda_aprovacao))
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);

        if (empty($estado_pedido_aguarda_inicializacao))
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);

        $empresa_id = request()->empresa_id;

        // dd($empresa_id);
        $baixas_farmacia = $this->baixa_farmacia->byEmpresa($empresa_id)
            // $baixas_farmacia = $this->baixa_farmacia->byEmpresa($empresa_id)
            ->where(function ($query) use ($estado_pedido_rejeiado, $estado_pedido_aguarda_aprovacao, $estado_pedido_aguarda_inicializacao) {
                $query->where('estado_baixa_id', $estado_pedido_rejeiado->id);
                $query->orWhere('estado_baixa_id', $estado_pedido_aguarda_aprovacao->id);
                $query->orWhere('estado_baixa_id', $estado_pedido_aguarda_inicializacao->id);
            })
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
                    // 'proveniencia' => $baixa_farmacia->proveniencia,
                    // 'beneficio_proprio_beneficiario' => $baixa_farmacia->beneficio_proprio_beneficiario,
                    'Nome Beneficiario' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : null,
                    'Nome Dependente' => !empty($baixa_farmacia->dependenteBeneficiario) ? $baixa_farmacia->dependenteBeneficiario->nome : null,
                    'Instituição' => $baixa_farmacia->farmacia->nome,
                    'Valor Baixa' => $baixa_farmacia->valor,
                    'Nº comprovativo' => $baixa_farmacia->nr_comprovativo,
                    // 'comprovativo' => $baixa_farmacia->comprovativo,
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
                    // 'comentario_baixa' => $comentario_baixa,
                    // 'comentario_pedido_aprovacao' => $baixa_farmacia->comentario_pedido_aprovacao,
                    // 'descricao' => $descricao,
                ];
            })->toArray();

        $nr_baixas_far = 0;

        /** @var BaixaUnidadeSanitaria $baixas_unidade_sanitaria*/
        $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
            ->byEmpresa($empresa_id)
            ->where(function ($query) use ($estado_pedido_rejeiado, $estado_pedido_aguarda_aprovacao, $estado_pedido_aguarda_inicializacao) {
                $query->where('estado_baixa_id', $estado_pedido_rejeiado->id);
                $query->orWhere('estado_baixa_id', $estado_pedido_aguarda_aprovacao->id);
                $query->orWhere('estado_baixa_id', $estado_pedido_aguarda_inicializacao->id);
            })
            //->where('estado_baixa_id', $estado_pedido_rejeiado->id)
            //->orWhere('estado_baixa_id', $estado_pedido_aguarda_aprovacao->id)
            //->orWhere('estado_baixa_id', $estado_pedido_aguarda_inicializacao->id)
            ->with(
                'beneficiario:id,nome',
                'dependenteBeneficiario:id,nome',
                'unidadeSanitaria:id,nome',
                'estadoBaixa:id,nome,codigo',
                'beneficiario:id,nome',
                'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,servico_id,baixa_unidade_sanitaria_id',
                'itensBaixaUnidadeSanitaria.servico:id,nome'
            )
            ->get(['id', 'valor', 'responsavel', 'proveniencia', 'comprovativo', 'data_criacao_pedido_aprovacao', 'data_aprovacao_pedido_aprovacao', 'resposavel_aprovacao_pedido_aprovacao', 'comentario_baixa', 'comentario_pedido_aprovacao', 'unidade_sanitaria_id', 'estado_baixa_id', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'created_at', 'updated_at'])
            ->map(function ($baixa_unidade_sanitaria) {

                $descricao = [];
                foreach ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria as $iten_baixa_unidade_sanitaria) {
                    $descricao_actual = [
                        'servico' => $iten_baixa_unidade_sanitaria->servico->nome,
                        'quantidade' => $iten_baixa_unidade_sanitaria->quantidade,
                        'preco' => $iten_baixa_unidade_sanitaria->preco,
                        'iva' => $iten_baixa_unidade_sanitaria->iva,
                        'preco_iva' => $iten_baixa_unidade_sanitaria->preco_iva,
                    ];
                    array_push($descricao, $descricao_actual);
                    $descricao_actual = [];
                }


                $responsavel = $baixa_unidade_sanitaria->responsavel;
                $comentario_baixa = $baixa_unidade_sanitaria->comentario_baixa;

                if (is_array($responsavel))
                    usort($responsavel, sort_desc_array_objects('data'));

                if (is_array($comentario_baixa))
                    usort($comentario_baixa, sort_desc_array_objects('data'));

                return [
                    // 'id' => $baixa_unidade_sanitaria->id,
                    // 'proveniencia' => $baixa_unidade_sanitaria->proveniencia,
                    // 'beneficio_proprio_beneficiario' => $baixa_unidade_sanitaria->beneficio_proprio_beneficiario,
                    'Beneficiario' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : null,
                    'Dependente' => !empty($baixa_unidade_sanitaria->dependenteBeneficiario) ? $baixa_unidade_sanitaria->dependenteBeneficiario->nome : null,
                    'Instiuição' => $baixa_unidade_sanitaria->unidadeSanitaria->nome,
                    'Valor baixa' => $baixa_unidade_sanitaria->valor,
                    // 'estado' => $baixa_unidade_sanitaria->estado,
                    // 'estado_id' => $baixa_unidade_sanitaria->estadoBaixa->id,
                    // 'estado_codigo' => $baixa_unidade_sanitaria->estadoBaixa->codigo,
                    'Estado' => $baixa_unidade_sanitaria->estadoBaixa->nome,
                    'Nº comprovativo' => $baixa_unidade_sanitaria->nr_comprovativo,
                    // 'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                    // 'estado_texto' => $baixa_unidade_sanitaria->estado_texto,
                    'Data baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                    // 'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                    // 'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                    // 'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                    // 'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                    // 'responsavel' => $responsavel,
                    // 'comentario_baixa' => $comentario_baixa,
                    // 'comentario_pedido_aprovacao' => $baixa_unidade_sanitaria->comentario_pedido_aprovacao,
                    // 'descricao' => $descricao,
                ];
            })->toArray();
        // dd($baixas_unidade_sanitaria);

        $nr_baixas_us = 0;


        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);

        // dd([$meses, $meses_cont]);

        $data = [
            'baixas' => $baixas,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia retrieved successfully!', 200);
    }

    public function aprovarPedidoAprovacao(Request $request)
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $user = Auth::user();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;

        $request->validate(['proveniencia' => 'required|integer', 'comentario_pedido_aprovacao' => 'nullable|string|max:255']);
        $estado_baixa = EstadoBaixa::where('codigo', '9')->first();
        if (empty($estado_baixa)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        $proveniencia = $request->proveniencia;
        $objecto_comentario_array = [
            'proveniencia' => 'Empresa',
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'data' => date('d-m-Y H:i:s', strtotime(now())),
            'comentario' => $request->comentario_pedido_aprovacao
        ];
        $objecto_responsavel_array = [
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'accao' => 'Aprovou o Pedido de Autorização',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        // $input = $request->only(['comentario_pedido_aprovacao']);
        $input['data_aprovacao_pedido_aprovacao'] = Carbon::now();
        $input['resposavel_aprovacao_pedido_aprovacao'] = !empty($utilizador_empresa) ? $utilizador_empresa->nome : null;
        $input['estado_baixa_id'] = $estado_baixa->id;
        $input['comentario_pedido_aprovacao'] = [];


        if (!empty($request->comentario_pedido_aprovacao)) {
            $comentario = $request->baixa->comentario_pedido_aprovacao;
            if (is_array($comentario)) {
                array_push($comentario, $objecto_comentario_array);
            } else {
                $comentario = array();
                array_push($comentario, $objecto_comentario_array);
            }

            $input['comentario_pedido_aprovacao'] = $comentario;
        }


        if (!empty($request->baixa->responsavel)) {
            $responsavel = $request->baixa->responsavel;
            if (is_array($responsavel)) {
                array_push($responsavel, $objecto_responsavel_array);
            } else {
                $responsavel = array();
                array_push($responsavel, $objecto_responsavel_array);
            }

            $input['responsavel'] = $responsavel;
        } else {
            $input['responsavel'] = [$objecto_responsavel_array];
        }

        DB::beginTransaction();
        try {
            if ($proveniencia == 1) {

                $baixa_farmacia = $request->baixa;
                $this->actualizarEstadoBaixa($baixa_farmacia, $input);
                DB::commit();
            } elseif ($proveniencia == 2) {

                $baixa_unidade_sanitaria = $request->baixa;
                $this->actualizarEstadoBaixa($baixa_unidade_sanitaria, $input);
                DB::commit();
            } else {
                DB::rollback();
                return $this->sendError('Provêninecia Desconhecida! Contacte o Administrador.', 404);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        return $this->sendSuccess('Baixa actualizada com sucesso!', 200);
    }


    public function aprovarPedidoAprovacaoBulk(Request $request)
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate([
            'baixas' => 'required|array',
            'baixas.*.proveniencia' => [
                'required',
                Rule::in([1, 2])
            ]
        ]);

        $input_baixas = $request->baixas;
        $empresa_id = $request->empresa_id;
        $estado_baixa = EstadoBaixa::where('codigo', '9')->first();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $objecto_responsavel_array = [
            'nome' => $utilizador_empresa->nome,
            'accao' => 'Aprovou o Pedido de Autorização',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];

        if (empty($estado_baixa)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        DB::beginTransaction();
        try {
            foreach ($input_baixas as $input_baixa) {
                if ($input_baixa['proveniencia'] == 1) {
                    $baixa_farmacia = $this->baixa_farmacia->byEmpresa($empresa_id)->find($input_baixa['id']);
                    if (empty($baixa_farmacia)) {
                        DB::rollback();
                        return $this->sendError('Baixa não encontrada!', 404);
                    }
                    $input = array();
                    $input['estado_baixa_id'] = $estado_baixa->id;

                    if (!empty($baixa_farmacia->responsavel)) {
                        $responsavel = $baixa_farmacia->responsavel;
                        if (is_array($responsavel)) {
                            array_push($responsavel, $objecto_responsavel_array);
                        } else {
                            $responsavel = array();
                            array_push($responsavel, $objecto_responsavel_array);
                        }

                        $input['responsavel'] = $responsavel;
                    } else {
                        $input['responsavel'] = [$objecto_responsavel_array];
                    }

                    $this->actualizarEstadoBaixa($baixa_farmacia, $input);
                } elseif ($input_baixa['proveniencia'] == 2) {

                    $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria->byEmpresa($empresa_id)->find($input_baixa['id']);
                    if (empty($baixa_unidade_sanitaria)) {
                        DB::rollback();
                        return $this->sendError('Baixa não encontrada!', 404);
                    }
                    $input = array();
                    $input['estado_baixa_id'] = $estado_baixa->id;

                    if (!empty($baixa_unidade_sanitaria->responsavel)) {
                        $responsavel = $baixa_unidade_sanitaria->responsavel;
                        if (is_array($responsavel)) {
                            array_push($responsavel, $objecto_responsavel_array);
                        } else {
                            $responsavel = array();
                            array_push($responsavel, $objecto_responsavel_array);
                        }

                        $input['responsavel'] = $responsavel;
                    } else {
                        $input['responsavel'] = [$objecto_responsavel_array];
                    }

                    $this->actualizarEstadoBaixa($baixa_unidade_sanitaria, $input);
                }
            }

            DB::commit();
            return $this->sendSuccess('Baixas actualizadas com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError('Erro ao actualizar as Baixas', 404);
        }
    }


    public function rejeitarPedidoAprovacao(Request $request)
    {
        if (Gate::denies('gerir pedido aprovação')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $user = Auth::user();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;

        $request->validate(['proveniencia' => 'required|integer', 'comentario_pedido_aprovacao' => 'required|string|max:255']);
        $estado_baixa = EstadoBaixa::where('codigo', '7')->first();
        if (empty($estado_baixa)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        $objecto_comentario_array = [
            'proveniencia' => 'Empresa',
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'data' => date('d-m-Y H:i:s', strtotime(now())),
            'comentario' => $request->comentario_pedido_aprovacao
        ];
        $objecto_responsavel_array = [
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'accao' => 'Rejeitou o Pedido de Autorização',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $proveniencia = $request->proveniencia;
        $input['data_aprovacao_pedido_aprovacao'] = Carbon::now();
        $input['resposavel_aprovacao_pedido_aprovacao'] = !empty($utilizador_empresa) ? $utilizador_empresa->nome : null;
        $input['estado_baixa_id'] = $estado_baixa->id;
        $input['comentario_pedido_aprovacao'] = [];

        if (!empty($request->comentario_pedido_aprovacao)) {
            $comentario = $request->baixa->comentario_pedido_aprovacao;
            if (is_array($comentario)) {
                array_push($comentario, $objecto_comentario_array);
            } else {
                $comentario = array();
                array_push($comentario, $objecto_comentario_array);
            }

            $input['comentario_pedido_aprovacao'] = $comentario;
            // dd($input);
        }

        if (!empty($request->baixa->responsavel)) {
            $responsavel = $request->baixa->responsavel;
            if (is_array($responsavel)) {
                array_push($responsavel, $objecto_responsavel_array);
            } else {
                $responsavel = array();
                array_push($responsavel, $objecto_responsavel_array);
            }

            $input['responsavel'] = $responsavel;
        } else {
            $input['responsavel'] = [$objecto_responsavel_array];
        }




        DB::beginTransaction();
        try {
            if ($proveniencia == 1) {

                $baixa_farmacia = $request->baixa;
                $this->actualizarEstadoBaixa($baixa_farmacia, $input);
                DB::commit();
            } elseif ($proveniencia == 2) {

                $baixa_unidade_sanitaria = $request->baixa;
                $this->actualizarEstadoBaixa($baixa_unidade_sanitaria, $input);
                DB::commit();
            } else {
                DB::rollback();
                return $this->sendError('Provêninecia Desconhecida! Contacte o Administrador.', 404);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        return $this->sendSuccess('Baixa actualizada com sucesso!', 200);
    }


    /**
     * Download a file of the specied Baixa
     * GET baixas/{proveniencia}/{id}/comprovativo/download/{ficheiro}
     * @param integer $proveniencia
     * @param integer $baixa_id
     * @param mixed $ficheiro
     * @return Response
     */
    public function downloadComprovativoBaixa($proveniencia, $baixa_id, $ficheiro)
    {
        if (Gate::denies('gerir baixa')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request()->empresa_id;

        if ($proveniencia == 1) {

            $baixa_farmacia = $this->baixa_farmacia->byEmpresa($empresa_id)->with('farmacia')->find($baixa_id);
            if (!$baixa_farmacia) {
                return $this->sendError('Baixa não encontrada!', 404);
            }

            // $path = kebab_case($baixa_farmacia->farmacia->nome) . '-' . $baixa_farmacia->farmacia->tenant_id . '/baixas/' . $baixa_farmacia->id . '/' . $ficheiro;
            $path = storage_path_farmacia($baixa_farmacia->farmacia->nome, $baixa_farmacia->farmacia->id, 'baixas') . "$baixa_farmacia->id/$ficheiro";
            return download_file_s3($path);
        } elseif ($proveniencia == 2) {

            $baixa_unidade_sanitaria = $this->baixa_unidade_sanitaria->byEmpresa($empresa_id)->with('unidadeSanitaria')->find($baixa_id);
            if (!$baixa_unidade_sanitaria) {
                return $this->sendError('Baixa não encontrada!', 404);
            }

            // $path = kebab_case($baixa_unidade_sanitaria->unidadeSanitaria->nome) . '-' . $baixa_unidade_sanitaria->unidadeSanitaria->tenant_id . '/baixas/' . $baixa_unidade_sanitaria->id . '/' . $ficheiro;
            $path = storage_path_u_sanitaria($baixa_unidade_sanitaria->unidadeSanitaria->nome, $baixa_unidade_sanitaria->unidadeSanitaria->id, 'baixas') . "$baixa_unidade_sanitaria->id/$ficheiro";
            return download_file_s3($path);
        } else {
            return $this->sendError('Baixa não encontrada!', 404);
        }
    }

    /**
     * Set the estado attribute of the specied Baixa
     * Auxiliar function
     * @param mixed $baixa
     * @param integer $estado
     * @return void
     */
    protected function actualizarEstadoBaixa($baixa, $input)
    {
        /* if ($baixa->isEstado($estado)) {
            throw new Exception('A Baixa já encontra-se ' . '\'' . $baixa->estado_texto . '\'');
        } */

        if ($baixa->isEstado($input['estado_baixa_id'])) {
            throw new Exception('A Baixa já encontra-se ' . '\'' . $baixa->estadoBaixa->nome . '\'');
        }

        $baixa->update($input);
    }

    // protected function reporItenStock($farmacia_id, $marca_medicamento_id, $quantidade_solicitada)
    // {
    //     $retorno = [];

    //     $iten_satock_farmacia = StockFarmacia::where([['farmacia_id', '=', $farmacia_id], ['marca_medicamento_id', '=', $marca_medicamento_id]])
    //         ->first();

    //     if(empty($iten_satock_farmacia)) {
    //         return;
    //     }

    //     $quantidade_disponivel = $iten_satock_farmacia->quantidade_disponivel;
    //     $quantidade_final = (int)$quantidade_disponivel + (int)$quantidade_solicitada;
    //     $iten_satock_farmacia->quantidade_disponivel = $quantidade_final;
    //     $iten_satock_farmacia->save();

    //     return $iten_satock_farmacia;
    // }

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
}
