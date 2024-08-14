<?php

namespace App\Http\Controllers\API\Mobile;

use App\Models\EstadoBaixa;
use Illuminate\Http\Request;
use App\Models\BaixaFarmacia;
use App\Models\ItenBaixaFarmacia;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\BaixaUnidadeSanitaria;
use App\Http\Controllers\AppBaseController;

class BaixaFarmaciaMobileController extends AppBaseController
{
    private $baixa_farmacia;
    private $baixa_unidade_sanitaria;
    private $estado_baixa;

    public function __construct(BaixaFarmacia $baixa_farmacia, BaixaUnidadeSanitaria $baixa_unidade_sanitaria, EstadoBaixa $estado_baixa)
    {
        $this->baixa_farmacia = $baixa_farmacia;
        $this->baixa_unidade_sanitaria = $baixa_unidade_sanitaria;
        $this->estado_baixa = $estado_baixa;
    }


    public function getPedidosAprovacao()
    {
        $estado_pedido_aprovacao_codigos = [7, 8, 9];
        $estados_pedido_aprovacao = $this->estado_baixa->whereIn('codigo', $estado_pedido_aprovacao_codigos)->pluck('id');
        // dd($estados_pedido_aprovacao);
        $baixas_farmacia = [];
        $baixas_unidade_sanitaria = [];
        $beneficiario_ou_dependente_campo = null;
        $beneficiario_ou_dependente_valor = null;
        $beneficiario = null;
        $cliente = Auth::user();
        $cliente->load(['beneficiario:id,empresa_id', 'dependenteBeneficiario:id,beneficiario_id', 'dependenteBeneficiario.beneficiario:id,empresa_id']);

        if (!empty($cliente->beneficiario) && !empty($cliente->dependenteBeneficiario)) {
            return $this->sendError('Este cliente encontra-se associado à uma conta de Beneficiário e à uma conta de Dependente. Contacte o Administrador', 400);
        } else if (!empty($beneficiario = $cliente->beneficiario)) {

            $beneficiario_ou_dependente_campo = 'beneficiario_id';
            $beneficiario_ou_dependente_valor = $beneficiario->id;
        } else if (!empty($dependente_beneficiario = $cliente->dependenteBeneficiario)) {

            $beneficiario_ou_dependente_campo = 'dependente_beneficiario_id';
            $beneficiario_ou_dependente_valor = $dependente_beneficiario->id;
            
        } else {
            return $this->sendError('A sua conta cliente não está associada à nenhuma conta de Beneficiário ou de Dependente!',404);
        }


        // $farmacia_id = request('farmacia_id');
        if (!empty($beneficiario_ou_dependente_campo)) {
            $baixas_farmacia = $this->baixa_farmacia
                ->where($beneficiario_ou_dependente_campo, $beneficiario_ou_dependente_valor)
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
                    $descricao = [];
                    if ($baixa_farmacia->itensBaixaFarmacia) {
                        foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                            // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                            $descricao_actual = [
                                'id' => $iten_baixa_farmacia->id,
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
                    }

                    $responsavel = $baixa_farmacia->responsavel;
                    $comentario_baixa = $baixa_farmacia->comentario_baixa;
                    $comentario_pedido_aprovacao = $baixa_farmacia->comentario_pedido_aprovacao;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    if (is_array($comentario_pedido_aprovacao))
                        usort($comentario_pedido_aprovacao, sort_desc_array_objects('data'));

                    return [
                        'id' => $baixa_farmacia->id,
                        'proveniencia' => $baixa_farmacia->proveniencia,
                        'nome_beneficiario' => $baixa_farmacia->beneficiario->nome,
                        'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                        'valor_baixa' => $baixa_farmacia->valor,
                        'comprovativo' => $baixa_farmacia->comprovativo,
                        'comentario_baixa' => $comentario_baixa,
                        // 'estado' => $baixa_farmacia->estado,
                        // 'estado_id' => $baixa_farmacia->estadoBaixa->id,
                        'estado_codigo' => $baixa_farmacia->estadoBaixa->codigo,
                        'estado_nome' => $baixa_farmacia->estadoBaixa->nome,
                        // 'estado_texto' => $baixa_farmacia->estado_texto,
                        // 'data_baixa' => empty($baixa_farmacia->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_farmacia->created_at)),
                        'data_criacao_pedido_aprovacao' => empty($baixa_farmacia->data_criacao_pedido_aprovacao) ? null : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_criacao_pedido_aprovacao)),
                        'data_aprovacao_pedido_aprovacao' => empty($baixa_farmacia->data_aprovacao_pedido_aprovacao) ? null : date('Y-m-d H:i:s', strtotime($baixa_farmacia->data_aprovacao_pedido_aprovacao)),
                        'updated_at' => empty($baixa_farmacia->updated_at) ? null : date('Y-m-d H:i:s', strtotime($baixa_farmacia->updated_at)),
                        'responsavel' => $responsavel,
                        'resposavel_aprovacao_pedido_aprovacao' => $baixa_farmacia->resposavel_aprovacao_pedido_aprovacao,
                        'comentario_pedido_aprovacao' => $comentario_pedido_aprovacao,
                        'descricao' => $descricao,
                    ];
                })->toArray();


            /** @var BaixaUnidadeSanitaria $baixas_unidade_sanitaria*/
            $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
                ->where($beneficiario_ou_dependente_campo, $beneficiario_ou_dependente_valor)
                ->whereIn('estado_baixa_id', $estados_pedido_aprovacao)
                ->with(
                    'unidadeSanitaria:id,nome',
                    'estadoBaixa:id,nome,codigo',
                    'beneficiario:id,nome',
                    'itensBaixaUnidadeSanitaria:id,preco,iva,preco_iva,quantidade,servico_id,baixa_unidade_sanitaria_id',
                    'itensBaixaUnidadeSanitaria.servico:id,nome'
                )
                ->get(['id', 'valor', 'responsavel', 'proveniencia', 'comprovativo', 'data_criacao_pedido_aprovacao', 'data_aprovacao_pedido_aprovacao', 'updated_at', 'resposavel_aprovacao_pedido_aprovacao', 'comentario_baixa', 'comentario_pedido_aprovacao', 'unidade_sanitaria_id', 'estado_baixa_id', 'beneficiario_id', 'created_at'])
                ->map(function ($baixa_unidade_sanitaria) {

                    if ($baixa_unidade_sanitaria->itensBaixaUnidadeSanitaria) {
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
                        }

                        $descricao_actual = [];
                    }

                    $responsavel = $baixa_unidade_sanitaria->responsavel;
                    $comentario_baixa = $baixa_unidade_sanitaria->comentario_baixa;
                    $comentario_pedido_aprovacao = $baixa_unidade_sanitaria->comentario_pedido_aprovacao;

                    if (is_array($responsavel))
                        usort($responsavel, sort_desc_array_objects('data'));

                    if (is_array($comentario_baixa))
                        usort($comentario_baixa, sort_desc_array_objects('data'));

                    if (is_array($comentario_pedido_aprovacao))
                        usort($comentario_pedido_aprovacao, sort_desc_array_objects('data'));

                    return [
                        'id' => $baixa_unidade_sanitaria->id,
                        'proveniencia' => $baixa_unidade_sanitaria->proveniencia,
                        'nome_beneficiario' => $baixa_unidade_sanitaria->beneficiario->nome,
                        'nome_instituicao' => $baixa_unidade_sanitaria->unidadeSanitaria->nome,
                        'valor_baixa' => $baixa_unidade_sanitaria->valor,
                        // 'estado' => $baixa_unidade_sanitaria->estado,
                        'estado_id' => $baixa_unidade_sanitaria->estadoBaixa->id,
                        'estado_codigo' => $baixa_unidade_sanitaria->estadoBaixa->codigo,
                        'estado_nome' => $baixa_unidade_sanitaria->estadoBaixa->nome,
                        'comprovativo' => $baixa_unidade_sanitaria->comprovativo,
                        'comentario_baixa' => $comentario_baixa,
                        // 'estado_texto' => $baixa_unidade_sanitaria->estado_texto,
                        // 'data_baixa' => empty($baixa_unidade_sanitaria->created_at) ? '' : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->created_at)),
                        'data_criacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao) ? null : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_criacao_pedido_aprovacao)),
                        'data_aprovacao_pedido_aprovacao' => empty($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao) ? null : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->data_aprovacao_pedido_aprovacao)),
                        'updated_at' => empty($baixa_unidade_sanitaria->updated_at) ? null : date('Y-m-d H:i:s', strtotime($baixa_unidade_sanitaria->updated_at)),
                        'responsavel' => $responsavel,
                        'resposavel_aprovacao_pedido_aprovacao' => $baixa_unidade_sanitaria->resposavel_aprovacao_pedido_aprovacao,
                        'comentario_pedido_aprovacao' => $comentario_pedido_aprovacao,
                        'descricao' => $descricao,
                    ];
                })->toArray();
        }

        // dd($baixas_farmacia);
        $pedidos_aprovacao = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);
        $data = [
            'pedidos_aprovacao' => $pedidos_aprovacao,
        ];

        return $this->sendResponse($data, 'Pedidos de Aprovação!', 200);
    }


    public function getOrdemReserva()
    {
        $estado_pedido_aprovacao_codigos = [20];
        $estados_pedido_aprovacao = $this->estado_baixa->whereIn('codigo', $estado_pedido_aprovacao_codigos)->pluck('id');
        // dd($estados_pedido_aprovacao);
        $baixas_farmacia = [];
        $beneficiario_ou_dependente_campo = null;
        $beneficiario_ou_dependente_valor = null;
        $beneficiario = null;
        $cliente = Auth::user();
        $cliente->load(['beneficiario:id,empresa_id', 'dependenteBeneficiario:id,beneficiario_id', 'dependenteBeneficiario.beneficiario:id,empresa_id']);

        if (!empty($cliente->beneficiario) && !empty($cliente->dependenteBeneficiario)) {
            return $this->sendError('Este cliente encontra-se associado à uma conta de Beneficiário e à uma conta de Dependente. Contacte o Administrador', 400);
        } else if (!empty($cliente->beneficiario)) {

            $beneficiario = $cliente->beneficiario;
            $beneficiario_ou_dependente_campo = 'beneficiario_id';
            $beneficiario_ou_dependente_valor = $beneficiario->id;
        } else if (!empty($dependente_beneficiario = $cliente->dependenteBeneficiario)) {

            $dependente_beneficiario = $cliente->dependenteBeneficiario;
            $beneficiario_ou_dependente_campo = 'dependente_beneficiario_id';
            $beneficiario_ou_dependente_valor = $dependente_beneficiario->id;
            
        } else {
            return $this->sendError('A sua conta cliente não está associada à nenhuma conta de Beneficiário ou de Dependente!',404);
        }


        // $farmacia_id = request('farmacia_id');
        if (!empty($beneficiario_ou_dependente_campo)) {
            $baixas_farmacia = $this->baixa_farmacia
                ->where($beneficiario_ou_dependente_campo, $beneficiario_ou_dependente_valor)
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
                    if ($baixa_farmacia->itensBaixaFarmacia) {
                        foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                            // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                            $iten_ordem_reserva = [
                                'id' => $iten_baixa_farmacia->id,
                                'marca_medicamento_id' => $iten_baixa_farmacia->marca_medicamento_id,
                                'medicamento_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->codigo : '',
                                'medicamento_nome_generico' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico->nome : '',
                                'forma' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma : '',
                                'dosagem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->dosagem : '',
                                'quantidade' => $iten_baixa_farmacia->quantidade,
                                'preco' => $iten_baixa_farmacia->preco,
                                'iva' => $iten_baixa_farmacia->iva,
                                'preco_iva' => $iten_baixa_farmacia->preco_iva,
                            ];
                            array_push($itens_ordem_reserva, $iten_ordem_reserva);
                            $iten_ordem_reserva = [];
                        }
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
                        'nome_beneficiario' => $baixa_farmacia->beneficiario->nome,
                        'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                        'valor_baixa' => $baixa_farmacia->valor,
                        'comprovativo' => $baixa_farmacia->comprovativo,
                        'comentario_baixa' => $comentario_baixa,
                        'responsavel' => $responsavel,
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
                        'itens_ordem_reserva' => $itens_ordem_reserva,
                    ];
                });
        }


        $data = [
            'ordens_reserva' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Ordens de Reserva!', 200);
    }


    public function efectuarOrdemReserva(Request $request)
    {
        $request->validate([
            'ordens_reserva' => 'required|array',
            'ordens_reserva.*.proveniencia' => 'required|integer',
            'ordens_reserva.*.valor' => 'required|numeric',
            'ordens_reserva.*.farmacia_id' => 'required|integer',
            'ordens_reserva.*.itens_ordem_reserva' => 'required|array',
        ]);
        $estado_baixa_aguarda_inicializacao_gasto = $this->estado_baixa->where('codigo', 20)->first();
        // $cliente = Auth::user();
        if (empty($estado_baixa_aguarda_inicializacao_gasto))
            return $this->sendError('Estado da Ordem de Reserva não encontrado. Contacte o Administrador!', 404);

        /* if (empty($cliente->beneficiario))
            return $this->sendError('O cliente não tem uma conta de Beneficiario associada. Contacte o Administrador!', 404); */

        $input = $request->ordens_reserva;
        $beneficiario = null;
        $dependente_beneficiario = null;
        $beneficiario_ou_dependente_campo = null;
        $beneficiario_ou_dependente_valor = null;
        $ordens_ids = [];

        $cliente = Auth::user();
        $cliente->load(['beneficiario:id,empresa_id', 'dependenteBeneficiario:id,beneficiario_id', 'dependenteBeneficiario.beneficiario:id,empresa_id']);

        if (!empty($cliente->beneficiario) && !empty($cliente->dependenteBeneficiario)) {

            return $this->sendError('Este cliente encontra-se associado à uma conta de Beneficiário e à uma conta de Dependente. Contacte o Administrador', 400);
        } else if (!empty($cliente->beneficiario)) {

            $beneficiario = $cliente->beneficiario;
            $beneficiario_ou_dependente_campo = 'beneficiario_id';
            $beneficiario_ou_dependente_valor = $beneficiario->id;
            
        } else if (!empty($cliente->dependenteBeneficiario)) {

            $dependente_beneficiario = $cliente->dependenteBeneficiario;
            $beneficiario_ou_dependente_campo = 'dependente_beneficiario_id';
            $beneficiario_ou_dependente_valor = $dependente_beneficiario->id;
            
            if (!empty($dependente_beneficiario->beneficiario)) {
                $beneficiario = $dependente_beneficiario->beneficiario;
            }else {
                return $this->sendError('Não foi encontrado o Beneficiário do Dependente associado à sua conta Cliente!',404);
            }
        } else {
            return $this->sendError('A sua conta cliente não está associada à nenhuma conta de Beneficiário ou de Dependente!',404);
        }

        // dd($input);
        // dd($request->all());
        DB::beginTransaction();
        try {
            foreach ($input as $ordem_reseva) {
                $objecto_responsavel_array = [
                    'nome' => $cliente->nome,
                    'accao' => 'Cliente: Submeteu a Ordem de Reserva',
                    'data' => date('d-m-Y H:i:s', strtotime(now()))
                ];
                $ordem_reseva['beneficiario_id'] = !empty($beneficiario) ? $beneficiario->id : null;
                $ordem_reseva['dependente_beneficiario_id'] = !empty($dependente_beneficiario) ? $dependente_beneficiario->id : null;
                $ordem_reseva['empresa_id'] = !empty($beneficiario) ? $beneficiario->empresa_id : null;
                $ordem_reseva['estado_baixa_id'] = $estado_baixa_aguarda_inicializacao_gasto->id;
                $ordem_reseva['responsavel'] = [$objecto_responsavel_array];

// dd($ordem_reseva);
                $ordem_registada_id = $this->salvarOrdemReserva($ordem_reseva);
                array_push($ordens_ids, $ordem_registada_id);
            }
            // return $this->sendResponse($ordens_ids, 200);
            DB::commit();
            // return $this->sendSuccess('Ordens de Reserva registados com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }






        $estado_pedido_aprovacao_codigos = [20];
        $estados_pedido_aprovacao = $this->estado_baixa->whereIn('codigo', $estado_pedido_aprovacao_codigos)->pluck('id');

        $baixas_farmacia = $this->baixa_farmacia
            ->where($beneficiario_ou_dependente_campo, $beneficiario_ou_dependente_valor)
            ->where('estado_baixa_id', $estados_pedido_aprovacao)
            ->whereIn('id', $ordens_ids)
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
                if ($baixa_farmacia->itensBaixaFarmacia) {
                    foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                        // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                        $iten_ordem_reserva = [
                            'id' => $iten_baixa_farmacia->id,
                            'marca_medicamento_id' => $iten_baixa_farmacia->marca_medicamento_id,
                            'medicamento_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->codigo : '',
                            'medicamento_nome_generico' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico->nome : '',
                            'forma' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma : '',
                            'dosagem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->dosagem : '',
                            'quantidade' => $iten_baixa_farmacia->quantidade,
                            'preco' => $iten_baixa_farmacia->preco,
                            'iva' => $iten_baixa_farmacia->iva,
                            'preco_iva' => $iten_baixa_farmacia->preco_iva,
                        ];
                        array_push($itens_ordem_reserva, $iten_ordem_reserva);
                        $iten_ordem_reserva = [];
                    }
                }

                return [
                    'id' => $baixa_farmacia->id,
                    'proveniencia' => $baixa_farmacia->proveniencia,
                    'nome_beneficiario' => $baixa_farmacia->beneficiario->nome,
                    'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                    'valor_baixa' => $baixa_farmacia->valor,
                    'comprovativo' => $baixa_farmacia->comprovativo,
                    'comentario_baixa' => $baixa_farmacia->comentario_baixa,
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
                    'itens_ordem_reserva' => $itens_ordem_reserva,
                ];
            });

        $data = [
            'ordens_reserva' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Ordens de Reserva!', 200);
    }

    // ORIGINAL, já pode ser removido
    /*     public function efectuarOrdemReserva(Request $request)
    {
        $request->validate([
            'ordens_reserva' => 'required|array',
            'ordens_reserva.*.proveniencia' => 'required|integer',
            'ordens_reserva.*.valor' => 'required|numeric',
            'ordens_reserva.*.farmacia_id' => 'required|integer',
            'ordens_reserva.*.itens_ordem_reserva' => 'required|array',
        ]);
        $estado_baixa_aguarda_inicializacao_gasto = $this->estado_baixa->where('codigo', 20)->first();
        $cliente = Auth::user();
        if (empty($estado_baixa_aguarda_inicializacao_gasto))
            return $this->sendError('Estado da Ordem de Reserva não encontrado. Contacte o Administrador!', 404);

        if (empty($cliente->beneficiario))
            return $this->sendError('O cliente não tem uma conta de Beneficiario associada. Contacte o Administrador!', 404);

        $input = $request->ordens_reserva;
        $ordens_ids = [];

        // dd($input);
        // dd($request->all());
        DB::beginTransaction();
        try {
            foreach ($input as $ordem_reseva) {
                $objecto_responsavel_array = [
                    'nome' => $cliente->nome,
                    'accao' => 'Cliente: Submeteu a Ordem de Reserva',
                    'data' => date('d-m-Y H:i:s', strtotime(now()))
                ];
                $ordem_reseva['beneficiario_id'] = $cliente->beneficiario_id;
                $ordem_reseva['empresa_id'] = $cliente->beneficiario->empresa_id;
                $ordem_reseva['estado_baixa_id'] = $estado_baixa_aguarda_inicializacao_gasto->id;
                $ordem_reseva['responsavel'] = [$objecto_responsavel_array];


                $ordem_registada_id = $this->salvarOrdemReserva($ordem_reseva);
                array_push($ordens_ids, $ordem_registada_id);
            }
            // return $this->sendResponse($ordens_ids, 200);
            DB::commit();
            // return $this->sendSuccess('Ordens de Reserva registados com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }






        $estado_pedido_aprovacao_codigos = [20];
        $estados_pedido_aprovacao = $this->estado_baixa->whereIn('codigo', $estado_pedido_aprovacao_codigos)->pluck('id');

        $baixas_farmacia = $this->baixa_farmacia
            ->where('beneficiario_id', $cliente->beneficiario_id)
            ->where('estado_baixa_id', $estados_pedido_aprovacao)
            ->whereIn('id', $ordens_ids)
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
                if ($baixa_farmacia->itensBaixaFarmacia) {
                    foreach ($baixa_farmacia->itensBaixaFarmacia as $iten_baixa_farmacia) {
                        // dd($iten_baixa_farmacia->marcaMedicamento->medicamento);
                        $iten_ordem_reserva = [
                            'id' => $iten_baixa_farmacia->id,
                            'marca_medicamento_id' => $iten_baixa_farmacia->marca_medicamento_id,
                            'medicamento_codigo' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->codigo : '',
                            'medicamento_nome_generico' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->nomeGenerico->nome : '',
                            'forma' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma : '',
                            'dosagem' => !empty($iten_baixa_farmacia->marcaMedicamento->medicamento) ? $iten_baixa_farmacia->marcaMedicamento->medicamento->dosagem : '',
                            'quantidade' => $iten_baixa_farmacia->quantidade,
                            'preco' => $iten_baixa_farmacia->preco,
                            'iva' => $iten_baixa_farmacia->iva,
                            'preco_iva' => $iten_baixa_farmacia->preco_iva,
                        ];
                        array_push($itens_ordem_reserva, $iten_ordem_reserva);
                        $iten_ordem_reserva = [];
                    }
                }

                return [
                    'id' => $baixa_farmacia->id,
                    'proveniencia' => $baixa_farmacia->proveniencia,
                    'nome_beneficiario' => $baixa_farmacia->beneficiario->nome,
                    'nome_instituicao' => $baixa_farmacia->farmacia->nome,
                    'valor_baixa' => $baixa_farmacia->valor,
                    'comprovativo' => $baixa_farmacia->comprovativo,
                    'comentario_baixa' => $baixa_farmacia->comentario_baixa,
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
                    'itens_ordem_reserva' => $itens_ordem_reserva,
                ];
            });

        $data = [
            'ordens_reserva' => $baixas_farmacia,
        ];

        return $this->sendResponse($data, 'Ordens de Reserva!', 200);
    } */

    protected function salvarOrdemReserva($ordem_reseva)
    {
        $input_baixa['proveniencia'] = $ordem_reseva['proveniencia'];
        $input_baixa['valor'] = $ordem_reseva['valor'];
        $input_baixa['farmacia_id'] = $ordem_reseva['farmacia_id'];
        $input_baixa['beneficiario_id'] = $ordem_reseva['beneficiario_id'];
        $input_baixa['dependente_beneficiario_id'] = $ordem_reseva['dependente_beneficiario_id'];
        $input_baixa['empresa_id'] = $ordem_reseva['empresa_id'];
        $input_baixa['estado_baixa_id'] = $ordem_reseva['estado_baixa_id'];
        $input_baixa['responsavel'] = $ordem_reseva['responsavel'];

        $itens_baixa_input = $ordem_reseva['itens_ordem_reserva'];
        // dd($input_baixa);
        $baixa_farmacia = $this->baixa_farmacia->create($input_baixa);
        foreach ($itens_baixa_input as $key => $iten_baixa_input) {
            // dd($iten_baixa_input);
            $iten_baixa_input['baixa_farmacia_id'] = $baixa_farmacia->id;
            $iten_baixa = new ItenBaixaFarmacia();
            $iten_baixa->create($iten_baixa_input);
        }
        /* $baixa_farmacia->load([
                'itensBaixaFarmacia:id,preco,iva,preco_iva,quantidade,marca_medicamento_id,baixa_farmacia_id',
                'itensBaixaFarmacia.marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                'itensBaixaFarmacia.marcaMedicamento.medicamento:id,codigo,dosagem,nome_generico_medicamento_id,forma_medicamento_id',
                'itensBaixaFarmacia.marcaMedicamento.medicamento.nomeGenerico:id,nome',
                'itensBaixaFarmacia.marcaMedicamento.medicamento.formaMedicamento:id,forma',
            ]);
            dd($baixa_farmacia); */
        return $baixa_farmacia->id;
    }
}
