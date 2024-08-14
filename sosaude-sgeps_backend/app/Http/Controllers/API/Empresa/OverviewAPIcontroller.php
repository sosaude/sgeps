<?php

namespace App\Http\Controllers\API\Empresa;

use Excel;
use Response;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use App\Models\DoencaCronica;
use Illuminate\Validation\Rule;
use App\Models\GrupoBeneficiario;
use Illuminate\Support\Facades\DB;
use App\Imports\BeneficiarioImport;
use Illuminate\Support\Facades\Gate;
use App\Models\DependenteBeneficiario;
use Illuminate\Database\QueryException;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Tenant\Empresa\CreateUpdateBeneficiarioFormRequest;
use App\Models\BaixaFarmacia;
use App\Models\BaixaUnidadeSanitaria;
use App\Models\Empresa;
use App\Models\UnidadeSanitaria;
use App\Models\OrcamentoEmpresa;

use function PHPSTORM_META\map;

class OverviewAPIcontroller extends AppBaseController
{
    //
    /** @var $beneficiario $dependente_beneficiario */
    private $empresa;
    private $user;
    private $beneficiario;
    private $dependente_beneficiario;
    private $grupo_beneficiario;
    private $doenca_cronica;
    private $baixa_farmacia;
    private $baixa_unidade_sanitaria;
    private $unidade_sanitaria;
    private $orcamento_empresa;

    public function __construct(
        Empresa $empresa,
        UnidadeSanitaria $unidade_sanitaria,
        User $user,
        Beneficiario $beneficiario,
        DependenteBeneficiario $dependente_beneficiario,
        GrupoBeneficiario $grupo_beneficiario,
        DoencaCronica $doenca_cronica,
        BaixaFarmacia $baixa_farmacia,
        BaixaUnidadeSanitaria $baixa_unidade_sanitaria,
        OrcamentoEmpresa $orcamento_empresa
    ) {
        $this->empresa = $empresa;
        $this->user = $user;
        $this->beneficiario = $beneficiario;
        $this->dependente_beneficiario = $dependente_beneficiario;
        $this->grupo_beneficiario = $grupo_beneficiario;
        $this->doenca_cronica = $doenca_cronica;
        $this->baixa_farmacia = $baixa_farmacia;
        $this->baixa_unidade_sanitaria = $baixa_unidade_sanitaria;
        $this->unidade_sanitaria = $unidade_sanitaria;
        $this->orcamento_empresa = $orcamento_empresa;
    }

    /**
     * Display a listing of the Beneficiario.
     * GET|HEAD /beneficiarios
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $empresa_id = $request->empresa_id;

        $beneficiarios_all = $this->beneficiario->byEmpresa($empresa_id)->get();
        $dependentes_all = $this->dependente_beneficiario->byEmpresa($empresa_id)->get();
        $ben_dep_all = array_merge($beneficiarios_all->toArray(),$dependentes_all->toArray());

        $beneficiarios_inactivos = $this->beneficiario->byEmpresa($empresa_id)->where('activo', false)->get();
        $dependentes_inactivos = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', false)->get();
        $ben_dep_inactivos = array_merge($beneficiarios_inactivos->toArray(),$dependentes_inactivos->toArray());

        $beneficiarios = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->get(['id','nome']);
        $dependentes = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', true)->get(['id','nome']);
        $beneficiarios_merged = array_merge($beneficiarios->toArray(), $dependentes->toArray());

        $bene_homem = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "M")->get();
        $dependente_homem = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "M")->get();
        $ben_dep_homem = array_merge($bene_homem->toArray(), $dependente_homem->toArray());


        $bene_mulher = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "F")->get();
        $dependente_mulher = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "F")->get();
        $ben_dep_mulher = array_merge($bene_mulher->toArray(), $dependente_mulher->toArray());

        $empresa = $this->empresa->where('id', $empresa_id)->with('farmacias')->first();

        if (empty($empresa)) {
            return $this->sendError('Farmacias Not Found!', 404);
        }

        $farmacias = $empresa->farmacias->map(function ($farmacia) {
            return $farmacia->only(['id', 'nome']);
        });

        $empresa = $this->empresa->where('id', $empresa_id)->with('unidadesSanitarias')->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa não encontrdad.');
        }

        $unidades_sanitarias = $empresa->unidadesSanitarias->map(function ($clinica) {
            return $clinica->only(['id', 'nome']);
        });


        $aguarda_pagamento = 0;
        $confirmacao_valor_total = 0;
        $pagamento_valor_total = 0;
        $aguarda_confirmacao = 0;
        $recusado = 0;
        $recusado_total = 0;
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

            $bene_far = 0;

            foreach($baixas_farmacia as $baixa) {
                if ($baixa['estado_id'] == 6) {
                    $bene_far += 1;
                    $aguarda_confirmacao += 1;
                    $confirmacao_valor_total += $baixa['valor_baixa'];
                } elseif ($baixa['estado_id'] == 7) {
                    $recusado += 1;
                    $recusado_total += $baixa['valor_baixa'];
                }elseif ($baixa['estado_id'] == 4) {
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
        //return response()->json($baixas_unidade_sanitaria, 200);
        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);
        $benes = [];
        $bene_cont = [];

        foreach($baixas as $bene){
            if($bene['estado_id'] == 6){
                $bene_nomes = $bene['nome_beneficiario'];

                if(in_array($bene_nomes, $benes)){
                    $key = array_search($bene_nomes, $benes);
                    $bene_cont[$key] = $bene_cont[$key] + 1;
                }
                else {
                    array_push($benes, $bene_nomes);
                    array_push($bene_cont, 1);
                }
                
            }
        }
        $bene_usuaram_ps = count($benes) == 0 ? 0 : round((count($benes)*100)/count($beneficiarios_merged), 2);
        $total_percent_bene = count($beneficiarios) == 0 ? 0 : (count($beneficiarios_merged)*100)/count($beneficiarios_merged);
        $bene_nao_usaram = $total_percent_bene == 0 ? 0 : round(($total_percent_bene - $bene_usuaram_ps), 2);
        // dd([$benes, count($benes), $teste, $teste2]);
        //dd($baixas_unidade_sanitaria);
        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);
        $valor_pago = 0;
        $valor_recusado = 0;
        $provedor = 0;
        $bene_us = 0;

        foreach($baixas_unidade_sanitaria as $baixa) {
            if ($baixa['estado_id'] == 6) {
                $aguarda_confirmacao += 1;
                $bene_us += 1;
                $confirmacao_valor_total += $baixa['valor_baixa'];
            } elseif ($baixa['estado_id'] == 7) {
                $recusado += 1;
                $recusado_total += $baixa['valor_baixa'];
            }
            elseif ($baixa['estado_id'] == 4) {
                $aguarda_pagamento += 1;
                $pagamento_valor_total += $baixa['valor_baixa'];
            }
        }


        $ben_total = count($beneficiarios_merged);
        $provedores = array_merge($farmacias->toArray(), $unidades_sanitarias->toArray());

        $beneficiario_h_perct = count($ben_dep_homem) == 0 ? 0 : round((count($ben_dep_homem) * 100) / count($beneficiarios_merged), 2);
        $beneficiario_m_perct = count($ben_dep_mulher) == 0 ? 0 : round((count($ben_dep_mulher) * 100) / count($beneficiarios_merged), 2);
        $dependentes_perct =  count($dependentes) == 0 ? 0 : round((count($dependentes) * 100) / $ben_total, 2);

        $bene_ps = $bene_far + $bene_us;

        $bene_ps_percet = $bene_ps == 0 ? 0 : round((($bene_ps)*100)/count($beneficiarios_merged), 2);
        $bene_nao_ps = 100 - $bene_ps_percet;

        $farmaciasList = $this->getFarm($empresa_id);
        $unidadesSanitariasList = $this->getUS($empresa_id);

        $todayDate = date("Y-m-d");
        $matrixBaixa = $this->getBaixasMatrix($empresa_id,0,0,'',$todayDate);
        $chart_labels = [];
        $chart_data = [];
        $dim_data = [];
        foreach($matrixBaixa as $mtx){
            $usname = $mtx->unidade_sanitaria;
            $dim_data = [intval($mtx->m1),intval($mtx->m2),intval($mtx->m3),intval($mtx->m4),intval($mtx->m5),intval($mtx->m6),intval($mtx->m7),intval($mtx->m8),intval($mtx->m9),intval($mtx->m10),intval($mtx->m11),intval($mtx->m12)];
            array_push($chart_labels, $mtx->unidade_sanitaria);
            array_push($chart_data, $dim_data);
        }

        $mmm = $this->getValorExecutadoOrcamento($empresa_id);



        $barChartData = [];
        $year = date('Y');
        $te = $this->extractExecutadoChartDataPerYear($empresa_id,$year);
        array_push($barChartData,$te);

        $orcamentoActual = $this->getTotalOrcamento($empresa_id,$year);
        $orcPerMonth = [];
        $orc = 0;
        foreach($orcamentoActual as $ort){
            $orc = intval($ort->totalOrcamento);
            $orcDivided = $orc / 12;
            for ($x = 0; $x <= 11; $x++) {
            array_push($orcPerMonth, $orcDivided);
            }
        }
        
        
        $barchart_labelsx = [];
        $barchart_datax = [];
        $dim_data2x = [];
        foreach($barChartData as $mt){
            foreach($mt as $oe){
                $usnamex = $oe->ano;
                $dim_data2x = [intval($oe->m1),intval($oe->m2),intval($oe->m3),intval($oe->m4),intval($oe->m5),intval($oe->m6),intval($oe->m7),intval($oe->m8),intval($oe->m9),intval($oe->m10),intval($oe->m11),intval($oe->m12)];
                array_push($barchart_labelsx, $usnamex);
                array_push($barchart_datax, $dim_data2x);
            }
        }


        $data = [
            'baixas' => $bene_far,
            'nr_beneficiarios_geral' => count($ben_dep_all),
            'nr_beneficiarios_inactivos' => count($ben_dep_inactivos),
            'nr_beneficiarios' => count($beneficiarios_merged),
            'valor_pago' => floatval($confirmacao_valor_total) ,
            'valor_recusado' => $recusado_total,
            'valor_faturado' => $pagamento_valor_total,
            'provedores' => count($provedores),
            'bene_homem_percent' =>  $beneficiario_h_perct,
            'bene_mulher_percent' =>  $beneficiario_m_perct,
            'dependentes_percent' => $dependentes_perct,
            'bene_ps' => [$bene_nao_usaram, $bene_usuaram_ps],
            'farmacias' => $farmaciasList,
            'unidades_sanitarias'=> $unidadesSanitariasList,
            'stacked_labels' => $chart_labels,
            'stacked_data' => $chart_data,
            'barchart_datax' => $barchart_datax,
            'barchart_labelsx' => $barchart_labelsx,
            'barchart_orcamento_mes' => $orcPerMonth,
            'valor_orcado' => $orc

        ];

        return $this->sendResponse($data, 'Overview retrieved successfully');
    }

    public function indexServicos($startDate,$endDate,$usId,$farmId,Request $request)
    {

        $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        }

        /** @var BaixaUnidadeSanitaria $baixas_unidade_sanitaria*/
        if($usId > 0){

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
            ->where('unidade_sanitaria_id', $usId)
            ->whereBetween('created_at',[$startDate,$endDate])
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
                    'id' => $baixa_unidade_sanitaria->id,
                    'valor_baixa' => $baixa_unidade_sanitaria->valor,
                    'descricao' => $descricao,
                ];
            });
        }
        else if($usId == 0 && $farmId == 0){
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
                    'id' => $baixa_unidade_sanitaria->id,
                    'valor_baixa' => $baixa_unidade_sanitaria->valor,
                    'descricao' => $descricao,
                ];
            });

        }
        else{
            $baixas_unidade_sanitaria = [];
        }

        return $this->sendResponse($baixas_unidade_sanitaria, 'Serviços retrieved successfully!', 200);
    }


    public function indexServicosFarm($startDate,$endDate,$farmId,$usId,Request $request)
    {

        $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        }

        /** @var BaixaUnidadeSanitaria $baixas_unidade_sanitaria*/
        if($farmId > 0){
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
            ->where('farmacia_id', $farmId)
            ->whereBetween('created_at',[$startDate,$endDate])
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->map(function ($baixa_farmacia) {

                $descricao = [];
                foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                    // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                    $descricao_actual = [
                        'servico' => 'Medicamentos',
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
                    'valor_baixa' => $baixa_farmacia->valor,
                    'descricao' => $descricao,
                ];
            });
        }
        else if($farmId == 0 && $usId == 0){
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
                        'servico' => 'Medicamentos',
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
                    'valor_baixa' => $baixa_farmacia->valor,
                    'descricao' => $descricao,
                ];
            });

        }
        else{
            $baixas_farmacia = [];
        }

        return $this->sendResponse($baixas_farmacia, 'Serviços retrieved successfully!', 200);
    }

    

    public function indexBeneficiario($startDate,$endDate,Request $request)
    {
        $empresa_id = $request->empresa_id;
        $beneficiarios = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->whereBetween('created_at',[$startDate,$endDate])->get();
        $dependentes = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', true)->whereBetween('created_at',[$startDate,$endDate])->get();
        $beneficiarios_merged = array_merge($beneficiarios->toArray(), $dependentes->toArray());

        $date = intval(Carbon::now()->format('Y')); 
        $ano_nascimento = [];
        $doencas = [];
        foreach($beneficiarios_merged as $bene){ 
            $bene_nascimento =intval(date("Y", strtotime($bene['data_nascimento'])));
            $bene_doencas = $bene['doenca_cronica_nome'];
            array_push($doencas, $bene_doencas);
            $idade = $date - $bene_nascimento;
            array_push($ano_nascimento, $idade);

        }

        // dd($doencas);

        $jovem = 0; 
        $adulto = 0; 
        $idoso = 0;

        foreach($ano_nascimento as $ano){
            if($ano < 20){
                $jovem += 1;
            }
            else if($ano > 20 && $ano < 50){
                $adulto += 1;
            }
            else if($ano > 50 && $ano < 80){
                $idoso += 1;
            }
        }
        $doencas_nome = [];
        $doencas_nome_cont = [];
        foreach($doencas as $doenca){
            foreach($doenca as $item){
                if(in_array($item, $doencas_nome)){
                    $key = array_search($item, $doencas_nome);
                    $doencas_nome_cont[$key] = $doencas_nome_cont[$key] + 1;
                }
                else{
                    array_push($doencas_nome, $item);
                    array_push($doencas_nome_cont, 1);
                }
            }
        }


        // $doencas_percent = array_map(function ($item) use ($doencas_nome){
        //     return round(($item*100)/count($doencas_nome), 2);
        // }, $doencas_nome_cont);
        $doencas_percent = [];
        foreach($doencas_nome_cont as $item){
            $percent_doenca = round((($item)*100)/array_sum($doencas_nome_cont), 2);
            array_push($doencas_percent, $percent_doenca);
        }

        

        // dd([$doencas_nome, $doencas_nome_cont]);

        $jovem_percent =  $jovem == 0 ? 0 : round(($jovem*100)/(count($beneficiarios_merged)), 2);
        $adulto_percent = $adulto == 0 ? : round(($adulto*100)/(count($beneficiarios_merged)), 2);
        $idoso_percent = $idoso == 0 ? 0 : round(($idoso*100)/(count($beneficiarios_merged)), 2);

        $data = [
            'nr_bene' => count($beneficiarios_merged),
            'bene_intervalos' => ["0 - 20", "21 - 50", "51-80"],
            'bene_faixas_etarias' => [$jovem_percent, $adulto_percent, $idoso_percent],
            'doencas_nomes' => $doencas_nome,
            'doencas_nomes_percent' => $doencas_percent, 
        ];

        return $this->sendResponse($data, 'Beneficiario faixa etaria retrieved successfully');
    }

    public function indexDashboard($startDate,$endDate,$farmaciaId,$usId,Request $request){

        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $empresa_id = $request->empresa_id;
        // $beneficiarios_all = $this->beneficiario->byEmpresa($empresa_id)->whereBetween('created_at',[$startDate,$endDate])->get();
        // $beneficiarios_inactivos = $this->beneficiario->byEmpresa($empresa_id)->where('activo', false)->whereBetween('created_at',[$startDate,$endDate])->get();
        // $beneficiarios = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->whereBetween('created_at',[$startDate,$endDate])->get();
        // $dependentes = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', true)->whereBetween('created_at',[$startDate,$endDate])->get();
        // $bene_homem = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "M")->whereBetween('created_at',[$startDate,$endDate])->get();
        // $bene_mulher = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "F")->whereBetween('created_at',[$startDate,$endDate])->get();

        $beneficiarios_all = $this->beneficiario->byEmpresa($empresa_id)->whereBetween('created_at',[$startDate,$endDate])->get();
        $dependentes_all = $this->dependente_beneficiario->byEmpresa($empresa_id)->whereBetween('created_at',[$startDate,$endDate])->get();
        $ben_dep_all = array_merge($beneficiarios_all->toArray(),$dependentes_all->toArray());

        $beneficiarios_inactivos = $this->beneficiario->byEmpresa($empresa_id)->where('activo', false)->whereBetween('created_at',[$startDate,$endDate])->get();
        $dependentes_inactivos = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', false)->whereBetween('created_at',[$startDate,$endDate])->get();
        $ben_dep_inactivos = array_merge($beneficiarios_inactivos->toArray(),$dependentes_inactivos->toArray());

        $beneficiarios = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->whereBetween('created_at',[$startDate,$endDate])->get(['id','nome']);
        $dependentes = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', true)->whereBetween('created_at',[$startDate,$endDate])->get(['id','nome']);
        $beneficiarios_merged = array_merge($beneficiarios->toArray(), $dependentes->toArray());

        $bene_homem = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "M")->whereBetween('created_at',[$startDate,$endDate])->get();
        $dependente_homem = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', true)->whereBetween('created_at',[$startDate,$endDate])->where('genero', "M")->get();
        $ben_dep_homem = array_merge($bene_homem->toArray(), $dependente_homem->toArray());


        $bene_mulher = $this->beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "F")->whereBetween('created_at',[$startDate,$endDate])->get();
        $dependente_mulher = $this->dependente_beneficiario->byEmpresa($empresa_id)->where('activo', true)->where('genero', "F")->whereBetween('created_at',[$startDate,$endDate])->get();
        $ben_dep_mulher = array_merge($bene_mulher->toArray(), $dependente_mulher->toArray());

        $empresa = $this->empresa->where('id', $empresa_id)->with('farmacias')->first();

        if (empty($empresa)) {
            return $this->sendError('Farmacias Not Found!', 404);
        }

        $farmacias = $empresa->farmacias->map(function ($farmacia) {
            return $farmacia->only(['id', 'nome']);
        });

        $empresa = $this->empresa->where('id', $empresa_id)->with('unidadesSanitarias')->first();

        if (empty($empresa)) {
            return $this->sendError('Empresa não encontrada.');
        }

        $unidades_sanitarias = $empresa->unidadesSanitarias->map(function ($clinica) {
            return $clinica->only(['id', 'nome']);
        });


        $aguarda_pagamento = 0;
        $confirmacao_valor_total = 0;
        $pagamento_valor_total = 0;
        $aguarda_confirmacao = 0;
        $recusado = 0;
        $recusado_total = 0;
        $baixas_farmacia = $this->baixa_farmacia
            ->byEmpresa($empresa_id)
            ->when($farmaciaId, function ($query, $farmaciaId) {
                return $query->where('farmacia_id', $farmaciaId);
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
            ->whereBetween('created_at',[$startDate,$endDate])
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

            $bene_far = 0;

            foreach($baixas_farmacia as $baixa) {
                if ($baixa['estado_id'] == 6) {
                    $bene_far += 1;
                    $aguarda_confirmacao += 1;
                    $confirmacao_valor_total += $baixa['valor_baixa'];
                } elseif ($baixa['estado_id'] == 7) {
                    $recusado += 1;
                    $recusado_total += $baixa['valor_baixa'];
                }
                elseif ($baixa['estado_id'] == 4) {
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
            ->when($usId, function ($query, $usId) {
                return $query->where('unidade_sanitaria_id', $usId);
            })
            ->whereBetween('created_at',[$startDate,$endDate])
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
        //return response()->json($baixas_unidade_sanitaria, 200);
        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);
        $benes = [];
        $bene_cont = [];

        foreach($baixas as $bene){
            if($bene['estado_id'] == 6){
                $bene_nomes = $bene['nome_beneficiario'];

                if(in_array($bene_nomes, $benes)){
                    $key = array_search($bene_nomes, $benes);
                    $bene_cont[$key] = $bene_cont[$key] + 1;
                }
                else {
                    array_push($benes, $bene_nomes);
                    array_push($bene_cont, 1);
                }
                
            }
        }

        $beneficiarios_merged_count = count($beneficiarios_merged) > 0 ? count($beneficiarios_merged) : 1;
        $bene_usuaram_ps = count($benes) > 0 ? round((count($benes)*100)/$beneficiarios_merged_count, 2): 0;
        $total_percent_bene = count($benes) > 0 ? ($beneficiarios_merged_count*100)/$beneficiarios_merged_count:0;
        $bene_nao_usaram =  $bene_usuaram_ps > 0 ? ($total_percent_bene - $bene_usuaram_ps): 0;
        // dd([$benes, count($benes), $teste, $teste2]);
        //dd($baixas_unidade_sanitaria);
        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);
        $valor_pago = 0;
        $valor_recusado = 0;
        $provedor = 0;
        $bene_us = 0;

        foreach($baixas_unidade_sanitaria as $baixa) {
            if ($baixa['estado_id'] == 6) {
                $aguarda_confirmacao += 1;
                $bene_us += 1;
                $confirmacao_valor_total += $baixa['valor_baixa'];
            }elseif ($baixa['estado_id'] == 7) {
                $recusado += 1;
                $recusado_total += $baixa['valor_baixa'];
            } 
            elseif ($baixa['estado_id'] == 4) {
                $aguarda_pagamento += 1;
                $pagamento_valor_total += $baixa['valor_baixa'];
            }
        }

        $ben_total = count($beneficiarios_merged);

        $provedores = array_merge($farmacias->toArray(), $unidades_sanitarias->toArray());

        $beneficiario_h_perct = count($ben_dep_homem) > 0 ? round((count($ben_dep_homem) * 100) / count($beneficiarios_merged), 2) : 0;
        $beneficiario_m_perct = count($ben_dep_mulher) > 0 ? round((count($ben_dep_mulher) * 100) / count($beneficiarios_merged), 2) : 0;
        $dependentes_perct =  count($dependentes) == 0 ? 0 : round((count($dependentes) * 100) / $ben_total, 2);

        $bene_ps = $bene_far + $bene_us;

        $bene_ps_percet = $bene_ps > 0 ? round((($bene_ps)*100)/$beneficiarios_merged_count, 2) : 0;
        $bene_nao_ps = 100 - $bene_ps_percet;

        $matrixBaixa = $this->getBaixasMatrix($empresa_id,$usId,$farmaciaId,$startDate,$endDate);
        $chart_labels = [];
        $chart_data = [];
        $dim_data = [];
        foreach($matrixBaixa as $mtx){
            $usname = $mtx->unidade_sanitaria;
            $dim_data = [intval($mtx->m1),intval($mtx->m2),intval($mtx->m3),intval($mtx->m4),intval($mtx->m5),intval($mtx->m6),intval($mtx->m7),intval($mtx->m8),intval($mtx->m9),intval($mtx->m10),intval($mtx->m11),intval($mtx->m12)];
            array_push($chart_labels, $mtx->unidade_sanitaria);
            array_push($chart_data, $dim_data);
        }

        $barChartData = [];
        $year = date('Y');
        $te = $this->extractExecutadoChartDataPerYear($empresa_id,$year);
        array_push($barChartData,$te);

        $orcamentoActual = $this->getTotalOrcamento($empresa_id,$year);
        $orcPerMonth = [];
        $orc = 0;
        foreach($orcamentoActual as $ort){
            $orc = intval($ort->totalOrcamento);
            $orcDivided = $orc / 12;
            for ($x = 0; $x <= 11; $x++) {
            array_push($orcPerMonth, $orcDivided);
            }
        }
        
        
        $barchart_labelsx = [];
        $barchart_datax = [];
        $dim_data2x = [];
        foreach($barChartData as $mt){
            foreach($mt as $oe){
                $usnamex = $oe->ano;
                $dim_data2x = [intval($oe->m1),intval($oe->m2),intval($oe->m3),intval($oe->m4),intval($oe->m5),intval($oe->m6),intval($oe->m7),intval($oe->m8),intval($oe->m9),intval($oe->m10),intval($oe->m11),intval($oe->m12)];
                array_push($barchart_labelsx, $usnamex);
                array_push($barchart_datax, $dim_data2x);
            }
        }


        $data = [
            'baixas' => $bene_far,
            'nr_beneficiarios_geral' => count($ben_dep_all),
            'nr_beneficiarios_inactivos' => count($ben_dep_inactivos),
            'nr_beneficiarios' => count($beneficiarios_merged),
            'valor_pago' => $confirmacao_valor_total,
            'valor_faturado' => $pagamento_valor_total,
            'valor_recusado' => $recusado_total,
            'provedores' => count($provedores),
            'bene_homem_percent' =>  $beneficiario_h_perct,
            'bene_mulher_percent' =>  $beneficiario_m_perct,
            'dependentes_percent' => $dependentes_perct,
            'bene_ps' => [$bene_nao_usaram, $bene_usuaram_ps],
            'stacked_labels' => $chart_labels,
            'stacked_data' => $chart_data,
            'barchart_datax' => $barchart_datax,
            'barchart_labelsx' => $barchart_labelsx,
            'barchart_orcamento_mes' => $orcPerMonth,
            'valor_orcado' => $orc
        ];

        return $this->sendResponse($data, 'Overview retrieved successfully');

    }

    public function getFarm($empId){
        $farmacias = DB::table('empresas')
                            ->join('empresa_farmacia', 'empresa_farmacia.empresa_id', '=', 'empresas.id')
                            ->join('farmacias', 'farmacias.id', '=', 'empresa_farmacia.farmacia_id')
                            ->select('farmacias.id','farmacias.nome')
                            ->where('empresas.id','=',$empId)
                            ->get();
        return $farmacias;
    }

    public function getUS($empId){
        $us = DB::table('empresas')
                            ->join('empresa_unidade_sanitaria', 'empresa_unidade_sanitaria.empresa_id', '=', 'empresas.id')
                            ->join('unidade_sanitarias', 'unidade_sanitarias.id', '=', 'empresa_unidade_sanitaria.unidade_sanitaria_id')
                            ->select('unidade_sanitarias.id','unidade_sanitarias.nome')
                            ->where('empresas.id','=',$empId)
                            ->get();

        return $us;
    }

    public function getBaixasMatrix($empId,$usId,$farmId,$startDate,$endDate){

        if($startDate == null){
            $startDate = '';
        }

        if($usId > 0){
            $results = DB::select(DB::raw("
            SELECT unidade_sanitaria,
            sum(m1) as m1,
            sum(m2) as m2,
            sum(m3) as m3,
            sum(m4) as m4,
            sum(m5) as m5,
            sum(m6) as m6,
            sum(m7) as m7,
            sum(m8) as m8,
            sum(m9) as m9,
            sum(m10) as m10,
            sum(m11) as m11,
            sum(m12) as m12
              FROM (
            
            SELECT DISTINCT id,unidade_sanitaria, january as m1, 
                        february as m2,
                        march as m3, 
                        april as m4,
                        may as m5,
                        june as m6,
                        july as m7,
                        august as m8,
                        september as m9,
                        october as m10,
                        november as m11,
                        december as m12
                        
                        FROM (
            select 
                        DISTINCT bus.id,
                        bus.created_at,
                        MONTH(bus.created_at) AS MES,
                        bus.valor,
                        us.nome as unidade_sanitaria,
                        eb.nome as estado,
                        bus.estado_baixa_id as estado_id,
                        emp.id as empresa_id,
                        CASE WHEN MONTH(bus.created_at) = 1 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as january,
                        CASE WHEN MONTH(bus.created_at) = 2 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as february,
                        CASE WHEN MONTH(bus.created_at) = 3 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as march,
                        CASE WHEN MONTH(bus.created_at) = 4 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as april,
                        CASE WHEN MONTH(bus.created_at) = 5 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as may,
                        CASE WHEN MONTH(bus.created_at) = 6 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as june,
                        CASE WHEN MONTH(bus.created_at) = 7 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as july,
                        CASE WHEN MONTH(bus.created_at) = 8 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as august,
                        CASE WHEN MONTH(bus.created_at) = 8 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as september,
                        CASE WHEN MONTH(bus.created_at) = 10 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as october,
                        CASE WHEN MONTH(bus.created_at) = 11 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as november,
                        CASE WHEN MONTH(bus.created_at) = 12 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as december
            
                        from baixa_unidade_sanitarias bus
                        inner join unidade_sanitarias us on us.id = bus.unidade_sanitaria_id
                        inner join empresa_unidade_sanitaria empus on empus.unidade_sanitaria_id = us.id
                        inner join empresas emp on emp.id = empus.empresa_id
                        inner join iten_baixa_unidade_sanitarias ibus on ibus.baixa_unidade_sanitaria_id = bus.id
                        inner join servicos serv on serv.id = ibus.servico_id
                        inner join estado_baixas eb on eb.id = bus.estado_baixa_id
                        where bus.empresa_id  =:empId and bus.unidade_sanitaria_id=:usId and bus.created_at between :startDate and :endDate
                        
                        ) AS baixas 
                        
                        ) AS baixas_sum
                        group by unidade_sanitaria
                    "),array('empId' => $empId,'usId' => $usId,'startDate' => $startDate,'endDate' => $endDate));

        }
        else if($usId == 0 && $farmId == 0){
            $results = DB::select(DB::raw("
            SELECT unidade_sanitaria,
            sum(m1) as m1,
            sum(m2) as m2,
            sum(m3) as m3,
            sum(m4) as m4,
            sum(m5) as m5,
            sum(m6) as m6,
            sum(m7) as m7,
            sum(m8) as m8,
            sum(m9) as m9,
            sum(m10) as m10,
            sum(m11) as m11,
            sum(m12) as m12
              FROM (
            
            SELECT DISTINCT id,unidade_sanitaria, january as m1, 
                        february as m2,
                        march as m3, 
                        april as m4,
                        may as m5,
                        june as m6,
                        july as m7,
                        august as m8,
                        september as m9,
                        october as m10,
                        november as m11,
                        december as m12
                        
                        FROM (
            select 
                        DISTINCT bus.id,
                        bus.created_at,
                        MONTH(bus.created_at) AS MES,
                        bus.valor,
                        us.nome as unidade_sanitaria,
                        eb.nome as estado,
                        bus.estado_baixa_id as estado_id,
                        emp.id as empresa_id,
                        CASE WHEN MONTH(bus.created_at) = 1 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as january,
                        CASE WHEN MONTH(bus.created_at) = 2 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as february,
                        CASE WHEN MONTH(bus.created_at) = 3 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as march,
                        CASE WHEN MONTH(bus.created_at) = 4 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as april,
                        CASE WHEN MONTH(bus.created_at) = 5 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as may,
                        CASE WHEN MONTH(bus.created_at) = 6 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as june,
                        CASE WHEN MONTH(bus.created_at) = 7 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as july,
                        CASE WHEN MONTH(bus.created_at) = 8 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as august,
                        CASE WHEN MONTH(bus.created_at) = 8 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as september,
                        CASE WHEN MONTH(bus.created_at) = 10 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as october,
                        CASE WHEN MONTH(bus.created_at) = 11 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as november,
                        CASE WHEN MONTH(bus.created_at) = 12 and bus.estado_baixa_id = 4 THEN 1 ELSE 0 END as december
            
                        from baixa_unidade_sanitarias bus
                        inner join unidade_sanitarias us on us.id = bus.unidade_sanitaria_id
                        inner join empresa_unidade_sanitaria empus on empus.unidade_sanitaria_id = us.id
                        inner join empresas emp on emp.id = empus.empresa_id
                        inner join iten_baixa_unidade_sanitarias ibus on ibus.baixa_unidade_sanitaria_id = bus.id
                        inner join servicos serv on serv.id = ibus.servico_id
                        inner join estado_baixas eb on eb.id = bus.estado_baixa_id
                        where bus.empresa_id  =:empId and bus.created_at between :startDate and :endDate
                        
                        ) AS baixas 
                        
                        ) AS baixas_sum
                        group by unidade_sanitaria
                    "),array('empId' => $empId,'startDate' => $startDate,'endDate' => $endDate));

        }
        else{
            
                $results = [];
        }
        
        
        return $results;
    }

    public function getValorExecutadoOrcamento($empId){
        $executadosUS = DB::table('baixa_unidade_sanitarias')
        ->join('unidade_sanitarias', 'unidade_sanitarias.id', '=', 'baixa_unidade_sanitarias.unidade_sanitaria_id')
        ->join('categoria_unidade_sanitarias', 'categoria_unidade_sanitarias.id', '=', 'unidade_sanitarias.categoria_unidade_sanitaria_id')
        ->join('empresa_unidade_sanitaria', 'empresa_unidade_sanitaria.unidade_sanitaria_id', '=', 'unidade_sanitarias.id')
        ->join('empresas', 'empresas.id', '=', 'empresa_unidade_sanitaria.empresa_id')
        ->join('iten_baixa_unidade_sanitarias', 'iten_baixa_unidade_sanitarias.baixa_unidade_sanitaria_id', '=', 'baixa_unidade_sanitarias.id')
        ->join('servicos', 'servicos.id', '=', 'iten_baixa_unidade_sanitarias.servico_id')
        ->join('estado_baixas', 'estado_baixas.id', '=', 'baixa_unidade_sanitarias.estado_baixa_id')
        ->select(
        DB::raw('categoria_unidade_sanitarias.nome as categoria'),
        'empresas.id',
        DB::raw('CASE WHEN year(baixa_unidade_sanitarias.created_at) = 2022 and baixa_unidade_sanitarias.estado_baixa_id = 6 THEN baixa_unidade_sanitarias.valor ELSE 0 END as valor1'),
        DB::raw('CASE WHEN year(baixa_unidade_sanitarias.created_at) = 2021 and baixa_unidade_sanitarias.estado_baixa_id = 6 THEN baixa_unidade_sanitarias.valor ELSE 0 END as valor2'),
        DB::raw('CASE WHEN year(baixa_unidade_sanitarias.created_at) = 2020 and baixa_unidade_sanitarias.estado_baixa_id = 6 THEN baixa_unidade_sanitarias.valor ELSE 0 END as valor3')
        )
        ->distinct()
        ->get()->toArray();


        $executadosFarm = DB::table('baixa_farmacias')
        ->join('farmacias', 'farmacias.id', '=', 'baixa_farmacias.farmacia_id')
        ->join('empresa_farmacia', 'empresa_farmacia.farmacia_id', '=', 'farmacias.id')
        ->join('empresas', 'empresas.id', '=', 'empresa_farmacia.empresa_id')
        ->join('estado_baixas', 'estado_baixas.id', '=', 'baixa_farmacias.estado_baixa_id')
        ->select(
        DB::raw('"Farmacias" as categoria'),
        'empresas.id',
        DB::raw('CASE WHEN year(baixa_farmacias.created_at) = 2022 and baixa_farmacias.estado_baixa_id = 6 THEN baixa_farmacias.valor ELSE 0 END as valor1'),
        DB::raw('CASE WHEN year(baixa_farmacias.created_at) = 2021 and baixa_farmacias.estado_baixa_id = 6 THEN baixa_farmacias.valor ELSE 0 END as valor2'),
        DB::raw('CASE WHEN year(baixa_farmacias.created_at) = 2020 and baixa_farmacias.estado_baixa_id = 6 THEN baixa_farmacias.valor ELSE 0 END as valor3')
        )
        ->distinct()
        ->get()->toArray();


        $merged = array_merge($executadosUS,$executadosFarm);

        return $merged;
    }

    public function getTotalOrcamento($empId,$ano){
        $orcamentoTotal = DB::table('orcamento_empresas')
        ->select(
        DB::raw('sum(orcamento_laboratorio + orcamento_farmacia + orcamento_clinica ) as totalOrcamento')
        )
        ->where('empresa_id',$empId)
        ->where('ano_de_referencia',$ano)
        ->where('executado',false)
        ->get()->toArray();

        return $orcamentoTotal;
    }


    public function extractExecutadoChartDataPerYear($empId,$ano){
$results = DB::select(DB::raw("

select $ano as ano, sum(m1) as m1,
sum(m2) as m2,
sum(m3) as m3,
sum(m4) as m4,
sum(m5) as m5,
sum(m6) as m6,
sum(m7) as m7,
sum(m8) as m8,
sum(m9) as m9,
sum(m10) as m10,
sum(m11) as m11,
sum(m12) as m12 FROM(
select  
sum(january) as m1,
sum(february) as m2,
sum(march) as m3,
sum(april) as m4,
sum(may) as m5,
sum(june) as m6,
sum(july) as m7,
sum(august) as m8,
sum(september) as m9,
sum(october) as m10,
sum(november) as m11,
sum(december) as m12
  FROM (
    select 
                DISTINCT bus.id,
            CASE WHEN MONTH(bus.created_at) = 1 and YEAR(bus.created_at) =   $ano  and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as january,
            CASE WHEN MONTH(bus.created_at) = 2 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as february,
            CASE WHEN MONTH(bus.created_at) = 3 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as march,
            CASE WHEN MONTH(bus.created_at) = 4 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as april,
            CASE WHEN MONTH(bus.created_at) = 5 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as may,
            CASE WHEN MONTH(bus.created_at) = 6 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as june,
            CASE WHEN MONTH(bus.created_at) = 7 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as july,
            CASE WHEN MONTH(bus.created_at) = 8 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as august,
            CASE WHEN MONTH(bus.created_at) = 8 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as september,
            CASE WHEN MONTH(bus.created_at) = 10 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as october,
            CASE WHEN MONTH(bus.created_at) = 11 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as november,
            CASE WHEN MONTH(bus.created_at) = 12 and YEAR(bus.created_at) =   $ano and bus.estado_baixa_id = 6 THEN bus.valor ELSE 0 END as december
               
                from baixa_unidade_sanitarias bus
                inner join unidade_sanitarias us on us.id = bus.unidade_sanitaria_id
                inner join categoria_unidade_sanitarias c_us on c_us.id = us.categoria_unidade_sanitaria_id
                inner join empresa_unidade_sanitaria empus on empus.unidade_sanitaria_id = us.id
                inner join empresas emp on emp.id = empus.empresa_id
                inner join iten_baixa_unidade_sanitarias ibus on ibus.baixa_unidade_sanitaria_id = bus.id
                inner join servicos serv on serv.id = ibus.servico_id
                inner join estado_baixas eb on eb.id = bus.estado_baixa_id 
WHERE bus.estado_baixa_id  = 6 and bus.empresa_id = $empId
) AS tt 

union
select  sum(january) as m1,
sum(february) as m2,
sum(march) as m3,
sum(april) as m4,
sum(may) as m5,
sum(june) as m6,
sum(july) as m7,
sum(august) as m8,
sum(september) as m9,
sum(october) as m10,
sum(november) as m11,
sum(december) as m12
FROM (
select 
        DISTINCT b_farmacias.id,
        CASE WHEN year(b_farmacias.created_at) = 2022 and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as t,
        CASE WHEN year(b_farmacias.created_at) = 2021 and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as tt,
        CASE WHEN YEAR(b_farmacias.created_at) = 2020 and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as ttt,
            CASE WHEN MONTH(b_farmacias.created_at) = 1 and YEAR(b_farmacias.created_at) =  $ano  and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as january,
            CASE WHEN MONTH(b_farmacias.created_at) = 2 and YEAR(b_farmacias.created_at) =  $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as february,
            CASE WHEN MONTH(b_farmacias.created_at) = 3 and YEAR(b_farmacias.created_at) =  $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as march,
            CASE WHEN MONTH(b_farmacias.created_at) = 4 and YEAR(b_farmacias.created_at) =  $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as april,
            CASE WHEN MONTH(b_farmacias.created_at) = 5 and YEAR(b_farmacias.created_at) =  $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as may,
            CASE WHEN MONTH(b_farmacias.created_at) = 6 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as june,
            CASE WHEN MONTH(b_farmacias.created_at) = 7 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as july,
            CASE WHEN MONTH(b_farmacias.created_at) = 8 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as august,
            CASE WHEN MONTH(b_farmacias.created_at) = 8 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as september,
            CASE WHEN MONTH(b_farmacias.created_at) = 10 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as october,
            CASE WHEN MONTH(b_farmacias.created_at) = 11 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as november,
            CASE WHEN MONTH(b_farmacias.created_at) = 12 and YEAR(b_farmacias.created_at) =   $ano and b_farmacias.estado_baixa_id = 6 THEN b_farmacias.valor ELSE 0 END as december
        
        from baixa_farmacias b_farmacias
        inner join farmacias farm on farm.id = b_farmacias.farmacia_id
        inner join empresa_farmacia emp_farm on emp_farm.farmacia_id = farm.id
        inner join empresas emp on emp.id = emp_farm.empresa_id
        inner join estado_baixas eb on eb.id = b_farmacias.estado_baixa_id
WHERE b_farmacias.estado_baixa_id  = 6 and b_farmacias.empresa_id = $empId
) AS tt 
) AS TTT "),["empId" => $empId,"ano" => $ano]);

        return $results;
    }
}
