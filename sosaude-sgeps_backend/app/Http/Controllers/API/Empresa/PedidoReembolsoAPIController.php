<?php

namespace App\Http\Controllers\API\Empresa;

use App\Models\Beneficiario;
use Illuminate\Http\Request;
use App\Models\GastoReembolso;
use App\Models\PedidoReembolso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\EstadoPedidoReembolso;
use App\Models\DependenteBeneficiario;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Tenant\Empresa\CreateUpdatePedidoReembolsoFormRequest;
use Carbon\Carbon;
use DateTime;

class PedidoReembolsoAPIController extends AppBaseController
{
    private $pedido_reembolso;
    private $gasto_reembolso;
    private $beneficiario;
    private $dependente_beneficiario;

    public function __construct(PedidoReembolso $pedido_reembolso, GastoReembolso $gasto_reembolso, Beneficiario $beneficiario, DependenteBeneficiario $dependente_beneficiario)
    {
        $this->middleware(['check.estado'])->only([
            'processarPagamentoPedidoReembolso',
            'devolverPedidoReembolso',
            'confirmarPedidoReembolso',
        ]);

        $this->pedido_reembolso = $pedido_reembolso;
        $this->gasto_reembolso = $gasto_reembolso;
        $this->beneficiario = $beneficiario;
        $this->dependente_beneficiario = $dependente_beneficiario;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexPedidoReembolso()
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        }

        $pedidos_reembolso = $this->pedido_reembolso
            ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
            ->byEmpresa($empresa_id)
            ->orderBy('updated_at', 'DESC')
            ->get(['id', 'empresa_id', 'unidade_sanitaria', 'servico_prestado', 'nr_comprovativo', 'valor', 'data', 'updated_at', /* 'estado', */ 'comentario', 'responsavel', 'comprovativo', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'estado_pedido_reembolso_id'])
            ->map(function ($pedido_reembolso) {
                $data = date('Y-m-d H:i:s', strtotime($pedido_reembolso->data));

                return [
                    'id' => $pedido_reembolso->id,
                    'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                    'servico_prestado' => $pedido_reembolso->servico_prestado,
                    'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                    'valor' => $pedido_reembolso->valor,
                    'data' => $pedido_reembolso->data,
                    'updated_at' => empty($pedido_reembolso->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($pedido_reembolso->updated_at)),
                    // 'estado' => $pedido_reembolso->estado,
                    // 'estado_texto' => $pedido_reembolso->estado_texto,
                    'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                    'estado_pedido_reembolso_nome' => $pedido_reembolso->estadoPedidoReembolso->nome,
                    'comentario' => $pedido_reembolso->comentario,
                    'responsavel' => !empty($pedido_reembolso->responsavel) ? $pedido_reembolso->responsavel : [],
                    'comprovativo' => $pedido_reembolso->comprovativo,
                    // 'comprovativo_link' => $pedido_reembolso->comprovativo_link,
                    'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                    'nome_beneficiario' => $pedido_reembolso->beneficiario ? $pedido_reembolso->beneficiario->nome : null,
                    'nome_dependente' => $pedido_reembolso->dependenteBeneficiario ? $pedido_reembolso->dependenteBeneficiario->nome : null,
                ];
            })->toArray();
        $aguarda_confirmacao = 0;
        $aguarda_pagamento = 0;
        $confirmacao_valor_total = 0;
        $pagamento_valor_total = 0;
        // $nr_baixas_esperando = 0;
        foreach ($pedidos_reembolso as $pedido) {
            if ($pedido['estado_pedido_reembolso_codigo'] == 10) {
                // $nr_baixas_esperando += 1;
                $aguarda_confirmacao += 1;
                $confirmacao_valor_total += $pedido['valor'];
            } elseif ($pedido['estado_pedido_reembolso_codigo'] == 11) {
                $aguarda_pagamento += 1;
                $pagamento_valor_total += $pedido['valor'];
            }
        }

        $meses = [];
        $meses_cont = [];
        foreach ($pedidos_reembolso as $baixa_item) {
            if ($baixa_item['estado_pedido_reembolso_codigo'] == 10) {
                // $baixa_mes = Carbon::parse($baixa_item['data_baixa'])->format('Y-m-d');
                $baixa_mes = intval(Carbon::parse($baixa_item['updated_at'])->format('m'));
                $data = DateTime::createFromFormat("!m", $baixa_mes)->format("F");

                if (in_array($data, $meses)) {
                    $key = array_search($data, $meses);
                    $meses_cont[$key] = $meses_cont[$key] + 1;
                } else {
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
       
        foreach ($pedidos_reembolso as $baixa_item) {
            if ($baixa_item['estado_pedido_reembolso_codigo'] == 10) {
                // $baixa_mes = Carbon::parse($baixa_item['data_baixa'])->format('Y-m-d');
                $baixa_mes = intval(Carbon::parse($baixa_item['updated_at'])->format('m'));
                $data = DateTime::createFromFormat("!m", $baixa_mes)->format("F");

                if(in_array($data, $months)){
                    $key = array_search($data, $months);
                    $meses_cont2[$key] = $meses_cont2[$key] + 1;
                }
                
            }
        }


        $meses_nomes = array_map(function ($item) use ($meses) {
            if ($item == 'January') {
                return "Janeiro";
            } elseif ($item == 'February') {
                return "Fevereiro";
            } elseif ($item == 'March') {
                return "Março";
            } elseif ($item == 'April') {
                return "Abril";
            } elseif ($item == 'May') {
                return "Maio";
            } elseif ($item == 'June') {
                return "Junho";
            } elseif ($item == 'July') {
                return "Julho";
            } elseif ($item == 'August') {
                return "Agosto";
            } elseif ($item == 'September') {
                return "Setembro";
            } elseif ($item == 'October') {
                return "Outubro";
            } elseif ($item == 'November') {
                return "Novembro";
            } elseif ($item == 'December') {
                return "Dezembro";
            }
        }, $meses);

        // dd([$meses, $meses_cont]);

        $data = [
            'baixas' => $pedidos_reembolso,
            'resumo' => [$aguarda_confirmacao, $aguarda_pagamento],
            'valor_total' => [round($confirmacao_valor_total, 2), round($pagamento_valor_total, 2)],
            'meses' => $meses_nomes,
            'meses_baixas' => $meses_cont,
            'meses2' => $monthst,
            'meses_baixas2' => $meses_cont2
        ];

        return $this->sendResponse($data, 'Pedidos de Reembolso devolvidos com sucesso!', 200);
    }
    public function indexPedidoReembolsoExcel()
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        }

        $pedidos_reembolso = $this->pedido_reembolso
            ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
            ->byEmpresa($empresa_id)
            ->orderBy('updated_at', 'DESC')
            ->get(['id', 'empresa_id', 'unidade_sanitaria', 'servico_prestado', 'nr_comprovativo', 'valor', 'data', 'updated_at', /* 'estado', */ 'comentario', 'responsavel', 'comprovativo', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'estado_pedido_reembolso_id'])
            ->map(function ($pedido_reembolso) {
                $data = date('Y-m-d H:i:s', strtotime($pedido_reembolso->data));

                return [
                    // 'id' => $pedido_reembolso->id,
                    'Unidade Sanitaria' => $pedido_reembolso->unidade_sanitaria,
                    'Serviço prestado' => $pedido_reembolso->servico_prestado,
                    'Nº comprovativo' => $pedido_reembolso->nr_comprovativo,
                    'Valor' => $pedido_reembolso->valor,
                    'Data' => $pedido_reembolso->data,
                    // 'updated_at' => empty($pedido_reembolso->updated_at) ? '' : date('Y-m-d H:i:s', strtotime($pedido_reembolso->updated_at)),
                    // 'estado' => $pedido_reembolso->estado,
                    // 'estado_texto' => $pedido_reembolso->estado_texto,
                    // 'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                    'Estado' => $pedido_reembolso->estadoPedidoReembolso->nome,
                    // 'Comentario' => $pedido_reembolso->comentario,
                    // 'responsavel' => !empty($pedido_reembolso->responsavel) ? $pedido_reembolso->responsavel : [],
                    // 'comprovativo' => $pedido_reembolso->comprovativo,
                    // 'comprovativo_link' => $pedido_reembolso->comprovativo_link,
                    // 'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                    'Beneficiario' => $pedido_reembolso->beneficiario ? $pedido_reembolso->beneficiario->nome : null,
                    'Dependente' => $pedido_reembolso->dependenteBeneficiario ? $pedido_reembolso->dependenteBeneficiario->nome : null,
                ];
            })->toArray();


        // dd([$meses, $meses_cont]);

        $data = [
            'baixas' => $pedidos_reembolso,
        ];

        return $this->sendResponse($data, 'Pedidos de Reembolso devolvidos com sucesso!', 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*  public function create()
    {
        $empresa_id = request('empresa_id');
        $beneficiarios = $this->beneficiario
            ->byEmpresa($empresa_id)
            ->where('activo', true)
            ->get(['id', 'numero_beneficiario', 'nome']);

        $dependentes_beneficiario = $this->dependente_beneficiario
            ->byEmpresa($empresa_id)
            ->where('activo', true)
            ->get(['id', 'nome']);

        $gastos_reembolso = $this->gasto_reembolso
            ->get(['id', 'nome']);

        $data = [
            'gastos_reembolso' => $gastos_reembolso,
            'benenficiarios' => $beneficiarios,
            'dependentes_beneficiario' => $dependentes_beneficiario
        ];

        return $this->sendResponse($data, '', 200);
    } */

    public function create()
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');
        $beneficiarios = $this->beneficiario
            ->with('dependentes:id,nome,beneficiario_id,activo')
            ->byEmpresa($empresa_id)
            ->where('activo', true)
            ->get(['id', 'numero_beneficiario', 'nome'])
            ->map(function ($beneficiario) {
                // $dependentes_beneficiario = [];

                // if(!empty($beneficiario->depen))
                $dependentes_beneficiario = $beneficiario->dependentes
                    ->filter(function ($dependente, $key) {
                        return $dependente->activo == true;
                    })
                    ->values()
                    ->map(function ($dependente) {
                        return [
                            'id' => $dependente->id,
                            'nome' => $dependente->nome,
                        ];
                    });
                return [
                    'id' => $beneficiario->id,
                    'nome' => $beneficiario->nome,
                    'dependentes_beneficiario' => $dependentes_beneficiario
                ];
            });

        /* $dependentes_beneficiario = $this->dependente_beneficiario
            ->byEmpresa($empresa_id)
            ->where('activo', true)
            ->get(['id', 'nome']); */

        $gastos_reembolso = $this->gasto_reembolso
            ->get(['id', 'nome']);

        $data = [
            'gastos_reembolso' => $gastos_reembolso,
            'beneficiarios' => $beneficiarios,
            // 'dependentes_beneficiario' => $dependentes_beneficiario
        ];

        return $this->sendResponse($data, '', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\CreateUpdatePedidoReembolsoFormRequest  $request
     * @return \Illuminate\Http\Response
     */
    /* public function store(CreateUpdatePedidoReembolsoFormRequest $request)
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate([
            'ficheiros' => 'nullable|array',
            'ficheiros.*' => 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
        ]);

        $estado_aguardando_confirmacao = EstadoPedidoReembolso::where('codigo', '11')->first();

        if (!empty($request->ficheiros)) {
            if (!$this->validarFicheiros($request)) {
                return $this->sendError('Ficheiros inválidos!', 403);
            }
        }

        if (empty($estado_aguardando_confirmacao))
            return $this->sendError('Estado do Pedido não identificado! Contacte o Administrador.', 404);

        $input = $request->validated();
        // $input['estado'] = 2;
        $input['estado_pedido_reembolso_id'] = $estado_aguardando_confirmacao->id;
        $input['comprovativo'] = [];
        $temp_nome_ficheiros = [];

        DB::beginTransaction();
        try {

            $pedido_reembolso = $this->pedido_reembolso->create($input);

            $path = kebab_case($pedido_reembolso->empresa->nome) . '-' . $pedido_reembolso->empresa->id . '/pedidos-reembolso/' . $pedido_reembolso->id . '/';

            if (!empty($request->ficheiros)) {
                foreach ($request->ficheiros as $ficheiro) {

                    $upload = upload_file($path, $ficheiro);

                    if (!$upload) {
                        DB::rollback();
                        return $this->sendError('O não foi possível efectuar o carregamento do(s) arquivo(s)!', 500);
                    } else {
                        array_push($temp_nome_ficheiros, $upload);
                    }
                }
            }
            // dd($pedido_reembolso);
            $pedido_reembolso->comprovativo = isset($pedido_reembolso->comprovativo) ? array_merge($pedido_reembolso->comprovativo, $temp_nome_ficheiros) : $temp_nome_ficheiros;
            $pedido_reembolso->save();

            DB::commit();
            $temp_nome_ficheiros = [];
            return $this->sendSuccess('Pedido de Reembolso criado com sucesso!', 200);
        } catch (\Exception $e) {
            // dd($temp_nome_ficheiros);
            DB::rollback();
            if (!empty($path)) {
                if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                    return $this->sendError('Não foram removidos todos ficheiros! ' . $e->getMessage());
                }
            }

            return $this->sendError($e->getMessage());
        }
    } */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function efectuarPedidoReembolso(CreateUpdatePedidoReembolsoFormRequest $request)
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        // dd($request->ficheiros);
        // $cliente = Auth::user();
        // dd($cliente->beneficiario);
        $input = $request->validated();

        if (!empty($request->ficheiros)) {
            if (!$this->validarFicheiros($request)) {
                return $this->sendError('Ficheiros inválidos!', 403);
            }
        }

        if ($request->beneficio_proprio_beneficiario == true) {

            $beneficiario = $this->beneficiario
                ->where('id', $request->beneficiario_id)
                ->where('activo', true)
                ->first();
            if (empty($beneficiario))
                return $this->sendError('Beneficiario não encontrado.', 404);

            $input['dependente_beneficiario_id'] = null;
        } else {

            $dependente_beneficiario = $this->dependente_beneficiario
                ->where('id', $request->dependente_beneficiario_id)
                ->where('activo', true)
                ->first();
            if (empty($dependente_beneficiario))
                return $this->sendError('Dependente Beneficiario não encontrado.', 404);

            $input['dependente_beneficiario_id'] = $request->dependente_beneficiario_id;
        }



        // dd($input);
        /* if ((empty($cliente->beneficiario)) || ($cliente->beneficiario->activo == false))
            return $this->sendError('O Cliente não está associado a nenhuma conta de Beneficiário ou a sua conta de Beneficiario encontra-se inactiva!', 404); */

        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $estado_aguardando_confirmacao = EstadoPedidoReembolso::where('codigo', '10')->first();
        if (empty($estado_aguardando_confirmacao))
            return $this->sendError('Estado do Pedido não identificado! Contacte o Administrador.', 404);


        $objecto_responsavel_array = [
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'accao' => 'Submeteu o Pedido de Reembolso',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $input['estado_pedido_reembolso_id'] = $estado_aguardando_confirmacao->id;
        $input['comprovativo'] = [];
        $input['comentario'] = [];
        $input['responsavel'] = [$objecto_responsavel_array];
        $temp_nome_ficheiros = [];

        if (!empty($request->comentario)) {

            $objecto_comentario_array = [
                'proveniencia' => 'Empresa',
                'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
                'data' => date('d-m-Y H:i:s', strtotime(now())),
                'comentario' => $request->comentario
            ];

            $comentario = [];
            array_push($comentario, $objecto_comentario_array);
            $input['comentario'] = $comentario;
        }


        DB::beginTransaction();
        try {

            $pedido_reembolso = $this->pedido_reembolso->create($input);

            // $path = kebab_case($pedido_reembolso->empresa->nome) . '-' . $pedido_reembolso->empresa->id . '/pedidos-reembolso/' . $pedido_reembolso->id . '/';
            $path = storage_path_empresa($pedido_reembolso->empresa->nome, $pedido_reembolso->empresa->id, 'pedidos-reembolso') . "$pedido_reembolso->id/";

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
            // dd($pedido_reembolso);
            $pedido_reembolso->comprovativo = isset($pedido_reembolso->comprovativo) ? array_merge($pedido_reembolso->comprovativo, $temp_nome_ficheiros) : $temp_nome_ficheiros;
            $pedido_reembolso->save();

            DB::commit();
            $temp_nome_ficheiros = [];
            $data = [
                'id' => $pedido_reembolso->id,
                'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                'servico_prestado' => $pedido_reembolso->servico_prestado,
                'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                'valor' => $pedido_reembolso->valor,
                'data' => $pedido_reembolso->data,
                // 'estado' => $pedido_reembolso->estado,
                // 'estado_texto' => $pedido_reembolso->estado_texto,
                'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                'responsavel' => !empty($pedido_reembolso->responsavel) ? $pedido_reembolso->responsavel : [],
                'comentario' => $pedido_reembolso->comentario,
                'comprovativo' => $pedido_reembolso->comprovativo,
                'comprovativo_link' => $pedido_reembolso->comprovativo_link,
                'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                'nome_beneficiario' => empty($pedido_reembolso->beneficiario) ? '' : $pedido_reembolso->beneficiario->nome,
                'nome_dependente' => empty($pedido_reembolso->dependenteBeneficiario) ? '' : $pedido_reembolso->dependenteBeneficiario->nome,
            ];
            return $this->sendResponse($data, 'Pedido de Reembolso criado com sucesso!', 200);
        } catch (\Exception $e) {
            // dd($temp_nome_ficheiros);
            DB::rollback();
            if (!empty($path)) {
                if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                    return $this->sendError('Não foram removidos todos ficheiros! ' . $e->getMessage());
                }
            }

            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Set a Pagamento Processado estado for specific PedidoReembolso in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /* public function processarPagamentoPedidoReembolso(Request $request)
    {
        $pedido_reembolso = $request->pedido_reembolso;

        DB::beginTransaction();
        try {
            $pedido_reembolso->update(['estado' => 3]);

            DB::commit();
            return $this->sendSuccess('Pedido de Reembolso actualizado com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    } */

    public function confirmarPedidoReembolso(Request $request)
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        // dd($request->all());
        $request->validate([
            'ficheiros' => 'nullable|array',
            // 'ficheiros.*' => 'required|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'ficheiros.*' => 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'comentario' => 'nullable|string|max:255',
            'comprovativo' => 'nullable',
        ]);

        if (!empty($request->ficheiros)) {
            if (!$this->validarFicheiros($request)) {
                return $this->sendError('Ficheiros inválidos!', 403);
            }
        }
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $pedido_reembolso = $request->pedido_reembolso;
        $temp_nome_ficheiros = [];



        $objecto_responsavel_array = [
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'accao' => 'Confirmou o Pedido de Reembolso',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $estado_aguardando_pagamento = EstadoPedidoReembolso::where('codigo', '11')->first();
        if (empty($estado_aguardando_pagamento))
            return $this->sendError('Estado do Pedido não identificado! Contacte o Administrador..', 404);

        $input['estado_pedido_reembolso_id'] = $estado_aguardando_pagamento->id;


        if (!empty($request->comentario)) {

            $objecto_comentario_array = [
                'proveniencia' => 'Empresa',
                'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
                'data' => date('d-m-Y H:i:s', strtotime(now())),
                'comentario' => $request->comentario
            ];

            if (!empty($request->pedido_reembolso->comentario)) {
                $comentario = $request->pedido_reembolso->comentario;
                if (is_array($comentario)) {
                    array_push($comentario, $objecto_comentario_array);
                } else {
                    $comentario = array();
                    array_push($comentario, $objecto_comentario_array);
                }

                $input['comentario'] = $comentario;
            } else {
                $input['comentario'] = [$objecto_comentario_array];
            }
        }



        if (!empty($request->pedido_reembolso->responsavel)) {
            $responsavel = $request->pedido_reembolso->responsavel;
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
            $pedido_reembolso->update($input);
            $pedido_reembolso->load('empresa');

            // $path = kebab_case($pedido_reembolso->empresa->nome) . '-' . $pedido_reembolso->empresa->id . '/pedidos-reembolso/' . $pedido_reembolso->id . '/';
            $path = storage_path_empresa($pedido_reembolso->empresa->nome, $pedido_reembolso->empresa->id, 'pedidos-reembolso') . "$pedido_reembolso->id/";

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
            // dd($pedido_reembolso);
            $pedido_reembolso->comprovativo = isset($pedido_reembolso->comprovativo) ? array_merge($pedido_reembolso->comprovativo, $temp_nome_ficheiros) : $temp_nome_ficheiros;
            $pedido_reembolso->save();
            $temp_nome_ficheiros = [];

            DB::commit();
            return $this->sendSuccess('Pedido de Reembolso actualizado com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            if (!empty($path)) {
                if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                    return $this->sendError('Não foram removidos todos ficheiros! ' . $e->getMessage());
                }
            }
            return $this->sendError($e->getMessage());
        }
    }


    public function confirmarPedidoReembolsoBulk(Request $request)
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate([
            'pedidos_reembolso' => 'required|array',
            'pedidos_reembolso.*.id' => 'required|integer'
        ]);

        $input_pedidos_reembolso = $request->pedidos_reembolso;
        $empresa_id = $request->empresa_id;
        $estado_aguardando_pagamento = EstadoPedidoReembolso::where('codigo', '11')->first();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $objecto_responsavel_array = [
            'nome' => $utilizador_empresa->nome,
            'accao' => 'Confirmou o Pedido de Reembolso',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];

        if (empty($estado_aguardando_pagamento)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        DB::beginTransaction();
        try {
            foreach ($input_pedidos_reembolso as $input_pedido_reembolso) {
                $pedido_reembolso = $this->pedido_reembolso->byEmpresa($empresa_id)->find($input_pedido_reembolso['id']);
                if (empty($pedido_reembolso)) {
                    DB::rollback();
                    return $this->sendError('Pedido de Reembolso não encontrado!', 404);
                }

                $input['estado_pedido_reembolso_id'] = $estado_aguardando_pagamento->id;

                if (!empty($pedido_reembolso->responsavel)) {
                    $responsavel = $pedido_reembolso->responsavel;
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

                $pedido_reembolso->update($input);
            }

            DB::commit();
            return $this->sendSuccess('Pedidos deReembolso actualizadas com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError('Erro ao actualizar os Pedidos de Reembolso', 404);
        }
    }

    public function processarPagamentoPedidoReembolso(Request $request)
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate([
            'ficheiros' => 'nullable|array',
            // 'ficheiros.*' => 'required|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'ficheiros.*' => 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'comentario' => 'nullable|string|max:255',
            'comprovativo' => 'nullable',
        ]);

        if (!empty($request->ficheiros)) {
            if (!$this->validarFicheiros($request)) {
                return $this->sendError('Ficheiros inválidos!', 403);
            }
        }

        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $pedido_reembolso = $request->pedido_reembolso;
        $temp_nome_ficheiros = [];
        $objecto_responsavel_array = [
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'accao' => 'Processou o Pagamento do Pedido de Reembolso',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $estado_pagamento_processado = EstadoPedidoReembolso::where('codigo', '12')->first();

        if (empty($estado_pagamento_processado))
            return $this->sendError('Estado do Pedido não identificado! Contacte o Administrador.', 404);

        $input['estado_pedido_reembolso_id'] = $estado_pagamento_processado->id;

        if (!empty($request->comentario)) {

            $objecto_comentario_array = [
                'proveniencia' => 'Empresa',
                'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
                'data' => date('d-m-Y H:i:s', strtotime(now())),
                'comentario' => $request->comentario
            ];

            if (!empty($request->pedido_reembolso->comentario)) {
                $comentario = $request->pedido_reembolso->comentario;
                if (is_array($comentario)) {
                    array_push($comentario, $objecto_comentario_array);
                } else {
                    $comentario = array();
                    array_push($comentario, $objecto_comentario_array);
                }

                $input['comentario'] = $comentario;
            } else {
                $input['comentario'] = [$objecto_comentario_array];
            }
        }

        if (!empty($request->pedido_reembolso->responsavel)) {
            $responsavel = $request->pedido_reembolso->responsavel;
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
            $pedido_reembolso->update($input);
            $pedido_reembolso->load('empresa');

            // $path = kebab_case($pedido_reembolso->empresa->nome) . '-' . $pedido_reembolso->empresa->id . '/pedidos-reembolso/' . $pedido_reembolso->id . '/';
            $path = storage_path_empresa($pedido_reembolso->empresa->nome, $pedido_reembolso->empresa->id, 'pedidos-reembolso') . "$pedido_reembolso->id/";

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
            // dd($pedido_reembolso);
            $pedido_reembolso->comprovativo = isset($pedido_reembolso->comprovativo) ? array_merge($pedido_reembolso->comprovativo, $temp_nome_ficheiros) : $temp_nome_ficheiros;
            $pedido_reembolso->save();
            $temp_nome_ficheiros = [];

            DB::commit();
            return $this->sendSuccess('Pedido de Reembolso actualizado com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            if (!empty($path)) {
                if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                    return $this->sendError('Não foram removidos todos ficheiros! ' . $e->getMessage());
                }
            }
            return $this->sendError($e->getMessage());
        }
    }


    public function processarPagamentoPedidoReembolsoBulk(Request $request)
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate([
            'pedidos_reembolso' => 'required|array',
            'pedidos_reembolso.*.id' => 'required|integer'
        ]);

        $input_pedidos_reembolso = $request->pedidos_reembolso;
        $empresa_id = $request->empresa_id;
        $estado_pagamento_processado = EstadoPedidoReembolso::where('codigo', '12')->first();
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $objecto_responsavel_array = [
            'nome' => $utilizador_empresa->nome,
            'accao' => 'Processou o Pagamento do Pedido de Reembolso',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];

        if (empty($estado_pagamento_processado)) {
            return $this->sendError('Estado do Porcesso não identificado, por favor contacte o Administrador!', 404);
        }

        DB::beginTransaction();
        try {
            foreach ($input_pedidos_reembolso as $input_pedido_reembolso) {
                $pedido_reembolso = $this->pedido_reembolso->byEmpresa($empresa_id)->find($input_pedido_reembolso['id']);
                if (empty($pedido_reembolso)) {
                    DB::rollback();
                    return $this->sendError('Pedido de Reembolso não encontrado!', 404);
                }

                $input['estado_pedido_reembolso_id'] = $estado_pagamento_processado->id;

                if (!empty($pedido_reembolso->responsavel)) {
                    $responsavel = $pedido_reembolso->responsavel;
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

                $pedido_reembolso->update($input);
            }

            DB::commit();
            return $this->sendSuccess('Pedidos deReembolso actualizadas com sucesso!', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError('Erro ao actualizar os Pedidos de Reembolso', 404);
        }
    }

    /**
     * Set a Aguardando Correcção estado for specific PedidoReembolso in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function devolverPedidoReembolso(Request $request)
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate(['comentario' => 'required|string|max:255']);
        $input = $request->only(['comentario']);
        $pedido_reembolso = $request->pedido_reembolso;
        $utilizador_empresa = Auth::user()->utilizadorEmpresa;
        $objecto_responsavel_array = [
            'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
            'accao' => 'Devolveu o Pedido de Reeembolso',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $estado_aguardando_correccao = EstadoPedidoReembolso::where('codigo', '13')->first();


        if (empty($estado_aguardando_correccao))
            return $this->sendError('Estado do Pedido não identificado! Contacte o Administrador.', 404);
        $input['estado_pedido_reembolso_id'] = $estado_aguardando_correccao->id;

        if (!empty($request->comentario)) {

            $objecto_comentario_array = [
                'proveniencia' => 'Empresa',
                'nome' => !empty($utilizador_empresa) ? $utilizador_empresa->nome : null,
                'data' => date('d-m-Y H:i:s', strtotime(now())),
                'comentario' => $request->comentario
            ];

            if (!empty($request->pedido_reembolso->comentario)) {
                $comentario = $request->pedido_reembolso->comentario;
                if (is_array($comentario)) {
                    array_push($comentario, $objecto_comentario_array);
                } else {
                    $comentario = array();
                    array_push($comentario, $objecto_comentario_array);
                }

                $input['comentario'] = $comentario;
            } else {
                $input['comentario'] = [$objecto_comentario_array];
            }
        }

        if (!empty($request->pedido_reembolso->responsavel)) {
            $responsavel = $request->pedido_reembolso->responsavel;
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
            $pedido_reembolso->update($input);

            DB::commit();
            return $this->sendSuccess('Pedido de Reembolso actualizado com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function downloadComprovativoPedidoReembolso($pedido_reembolso_id, $ficheiro)
    {
        if (Gate::denies('gerir pedido reembolso')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request()->empresa_id;

        $pedido_reembolso = $this->pedido_reembolso->byEmpresa($empresa_id)->with('empresa')->find($pedido_reembolso_id);
        if (empty($pedido_reembolso)) {
            return $this->sendError('Pedido de Reembolso não encontrado!', 404);
        }

        // $path = kebab_case($pedido_reembolso->empresa->nome) . '-' . $pedido_reembolso->empresa->id . '/pedidos-reembolso/' . $pedido_reembolso->id . '/' . $ficheiro;
        $path = storage_path_empresa($pedido_reembolso->empresa->nome, $pedido_reembolso->empresa->id, 'pedidos-reembolso') . "$pedido_reembolso->id/$ficheiro";
        return download_file_s3($path);
    }

    protected function validarFicheiros($request): bool
    {

        if ($request->hasFile('ficheiros')) {

            foreach ($request->ficheiros as $ficheiro) {
                if (!$ficheiro->isValid()) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /* public function upload($path, $ficheiro)
    {
    $extensao = $ficheiro->getClientOriginalExtension();
    $nome_ficheiro = rand(1000000, 1000000000) . '.' . $extensao;
    $upload = $ficheiro->storeAs($path, $nome_ficheiro);

    if (!$upload) {
    return null;
    } else {
    return basename($upload);
    }
    } */

    public function apagarFicheiros($path, $ficheiros)
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

    /* protected function apagarFicheiro($ficheiro, $path)
    {
    if ($ficheiro && Storage::exists($path . $ficheiro)) {
    if (Storage::delete($path . $ficheiro)) {
    return true;
    }
    }
    return false;
    } */

    /* protected function download($path)
    {
    if (Storage::exists($path)) {
    return response()->download(storage_path('app/public/' . $path));
    } else {
    return $this->sendError('Ficheiro não localizado!', 404);
    }
    } */

    public function testeCustomHelpers()
    {
        /*$ path = 'empresa-marrar-5/pedidos-reembolso/10/14763732.jpg';
        return download_files($path); */
        return delete_file('empresa-marrar-5/pedidos-reembolso/10/14763732.jpg');
    }
}
