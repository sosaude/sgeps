<?php

namespace App\Http\Controllers\API\Mobile;

use Illuminate\Http\Request;
use App\Models\GastoReembolso;
use App\Models\PedidoReembolso;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\EstadoPedidoReembolso;
use App\Models\DependenteBeneficiario;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Tenant\Mobile\CreateUpdatePedidoReembolsoFormRequest;

class PedidoReembolsoAPIController extends AppBaseController
{
    private $pedido_reembolso;
    private $gasto_reembolso;
    private $dependente_beneficiario;

    public function __construct(PedidoReembolso $pedido_reembolso, GastoReembolso $gasto_reembolso, DependenteBeneficiario $dependente_beneficiario)
    {
        $this->middleware(['check.estado'])->only([
            'processarPagamentoPedidoReembolso',
            'devolverPedidoReembolso',
        ]);

        $this->pedido_reembolso = $pedido_reembolso;
        $this->gasto_reembolso = $gasto_reembolso;
        $this->dependente_beneficiario = $dependente_beneficiario;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $estado_pedido_reembolso = null)
    {
        $pedidos_reembolso = [];
        // $beneficiario_id = $request->beneficiario_id;
        $dependente_beneficiario_id = $request->dependente_beneficiario_id;
        $cliente = Auth::user();
        // $dependentes = DependenteBeneficiario::where('beneficiario_id', $cliente->beneficiario_id)->pluck('id');
        // dd($dependentes);
        // dd($cliente);
        /* $empresa_id = request()->empresa_id;
        if (!$empresa_id) {
            return $this->sendError('Empresa não informada!', 404);
        } */


        if (!empty($cliente->beneficiario_id)) {

            if ($estado_pedido_reembolso) {

                $estado_pedido_reembolso = EstadoPedidoReembolso::where('codigo', $estado_pedido_reembolso)->first();
                if (empty($estado_pedido_reembolso))
                    return $this->sendError('Estado do Pedido informado não identificado!', 404);

                $pedidos_reembolso = $this->pedido_reembolso
                    ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
                    ->byBeneficiario($cliente->beneficiario_id)
                    ->where('estado_pedido_reembolso_id', $estado_pedido_reembolso->id)
                    ->get(['id', 'empresa_id', 'unidade_sanitaria', 'servico_prestado', 'nr_comprovativo', 'valor', 'data', /* 'estado', */ 'comentario', 'responsavel', 'comprovativo', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'estado_pedido_reembolso_id'])
                    ->map(function ($pedido_reembolso) {
                        $data = date('Y-m-d H:i:s', strtotime($pedido_reembolso->data));

                        $responsavel = $pedido_reembolso->responsavel;
                        $comentario = $pedido_reembolso->comentario;

                        if (is_array($responsavel))
                            usort($responsavel, sort_desc_array_objects('data'));

                        if (is_array($comentario))
                            usort($comentario, sort_desc_array_objects('data'));

                        return [
                            'id' => $pedido_reembolso->id,
                            'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                            'servico_prestado' => $pedido_reembolso->servico_prestado,
                            'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                            'valor' => $pedido_reembolso->valor,
                            'data' => $pedido_reembolso->data,
                            // 'estado' => $pedido_reembolso->estado,
                            // 'estado_texto' => $pedido_reembolso->estado_texto,
                            'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                            'comentario' => $comentario,
                            'responsavel' => $responsavel,
                            'comprovativo' => $pedido_reembolso->comprovativo,
                            'comprovativo_link' => $pedido_reembolso->comprovativo_link,
                            'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                            'nome_beneficiario' => empty($pedido_reembolso->beneficiario) ? '' : $pedido_reembolso->beneficiario->nome,
                            'nome_dependente' => empty($pedido_reembolso->dependenteBeneficiario) ? '' : $pedido_reembolso->dependenteBeneficiario->nome,
                        ];
                    });
            } else {
                $pedidos_reembolso = $this->pedido_reembolso
                    ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
                    ->byBeneficiario($cliente->beneficiario_id)
                    ->get(['id', 'empresa_id', 'unidade_sanitaria', 'servico_prestado', 'nr_comprovativo', 'valor', 'data', /* 'estado', */ 'comentario', 'responsavel', 'comprovativo', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'estado_pedido_reembolso_id'])
                    ->map(function ($pedido_reembolso) {
                        $data = date('Y-m-d H:i:s', strtotime($pedido_reembolso->data));

                        $responsavel = $pedido_reembolso->responsavel;
                        $comentario = $pedido_reembolso->comentario;

                        if (is_array($responsavel))
                            usort($responsavel, sort_desc_array_objects('data'));

                        if (is_array($comentario))
                            usort($comentario, sort_desc_array_objects('data'));

                        return [
                            'id' => $pedido_reembolso->id,
                            'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                            'servico_prestado' => $pedido_reembolso->servico_prestado,
                            'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                            'valor' => $pedido_reembolso->valor,
                            'data' => $pedido_reembolso->data,
                            // 'estado' => $pedido_reembolso->estado,
                            // 'estado_texto' => $pedido_reembolso->estado_texto,
                            'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                            'comentario' => $comentario,
                            'responsavel' => $responsavel,
                            'comprovativo' => $pedido_reembolso->comprovativo,
                            'comprovativo_link' => $pedido_reembolso->comprovativo_link,
                            'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                            'nome_beneficiario' => empty($pedido_reembolso->beneficiario) ? '' : $pedido_reembolso->beneficiario->nome,
                            'nome_dependente' => empty($pedido_reembolso->dependenteBeneficiario) ? '' : $pedido_reembolso->dependenteBeneficiario->nome,
                        ];
                    });
            }
        }/*  elseif (!empty($cliente->dependente_beneficiario_id)) {
            if($estado_pedido_reembolso) {
                $pedidos_reembolso = $this->pedido_reembolso
                ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
                ->byDependenteBeneficiario($dependente_beneficiario_id)
                ->get(['id', 'unidade_sanitaria', 'servico_prestado', 'nr_comprovativo', 'valor', 'data', 'comentario', 'comprovativo', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'estado_pedido_reembolso_id'])
                ->map(function ($pedido_reembolso) {
                    $data = date('Y-m-d H:i:s', strtotime($pedido_reembolso->data));

                    return [
                        'id' => $pedido_reembolso->id,
                        'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                        'servico_prestado' => $pedido_reembolso->servico_prestado,
                        'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                        'valor' => $pedido_reembolso->valor,
                        'data' => $pedido_reembolso->data,
                        // 'estado' => $pedido_reembolso->estado,
                        // 'estado_texto' => $pedido_reembolso->estado_texto,
                        'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                        'comentario' => $pedido_reembolso->comentario,
                        'comprovativo' => $pedido_reembolso->comprovativo,
                        'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                        'nome_beneficiario' => empty($pedido_reembolso->beneficiario) ? '' : $pedido_reembolso->beneficiario->nome,
                        'nome_dependente' => empty($pedido_reembolso->dependenteBeneficiario) ? '' : $pedido_reembolso->dependenteBeneficiario->nome,
                    ];
                });
            } else {
                $pedidos_reembolso = $this->pedido_reembolso
                ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
                ->byDependenteBeneficiario($dependente_beneficiario_id)
                ->get(['id', 'unidade_sanitaria', 'servico_prestado', 'nr_comprovativo', 'valor', 'data', 'comentario', 'comprovativo', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'estado_pedido_reembolso_id'])
                ->map(function ($pedido_reembolso) {
                    $data = date('Y-m-d H:i:s', strtotime($pedido_reembolso->data));

                    return [
                        'id' => $pedido_reembolso->id,
                        'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                        'servico_prestado' => $pedido_reembolso->servico_prestado,
                        'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                        'valor' => $pedido_reembolso->valor,
                        'data' => $pedido_reembolso->data,
                        // 'estado' => $pedido_reembolso->estado,
                        // 'estado_texto' => $pedido_reembolso->estado_texto,
                        'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                        'comentario' => $pedido_reembolso->comentario,
                        'comprovativo' => $pedido_reembolso->comprovativo,
                        'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                        'nome_beneficiario' => empty($pedido_reembolso->beneficiario) ? '' : $pedido_reembolso->beneficiario->nome,
                        'nome_dependente' => empty($pedido_reembolso->dependenteBeneficiario) ? '' : $pedido_reembolso->dependenteBeneficiario->nome,
                    ];
                });
            }
        } else {
            return $this->sendResponse([], 'Pedidos de Reembolso devolvidos com sucesso!', 200);
        } */

        $data = [
            'pedidos_reembolso' => $pedidos_reembolso,
        ];
        return $this->sendResponse($data, 'Pedidos de Reembolso devolvidos com sucesso!', 200);
    }


    /*     public function indexPedidoReembolsoByEstado($estado_pedido_reembolso)
    {
        $cliente = Auth::user();
        $pedidos_reembolso = null;
        $estado_pedido_reembolso = EstadoPedidoReembolso::where('codigo', $estado_pedido_reembolso)->first();

        if (empty($estado_pedido_reembolso))
            return $this->sendError('Estado do Pedido não encontrado!', 404);

        if (!empty($cliente->beneficiario_id)) {

            $pedidos_reembolso = $this->pedido_reembolso
                ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
                ->byBeneficiario($cliente->beneficiario_id)
                ->where('estado_pedido_reembolso_id', $estado_pedido_reembolso->id)
                ->get(['id', 'unidade_sanitaria', 'servico_prestado', 'nr_comprovativo', 'valor', 'data', 'comentario', 'comprovativo', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'estado_pedido_reembolso_id'])
                ->map(function ($pedido_reembolso) {
                    $data = date('Y-m-d H:i:s', strtotime($pedido_reembolso->data));

                    return [
                        'id' => $pedido_reembolso->id,
                        'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                        'servico_prestado' => $pedido_reembolso->servico_prestado,
                        'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                        'valor' => $pedido_reembolso->valor,
                        'data' => $pedido_reembolso->data,
                        'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                        'comentario' => $pedido_reembolso->comentario,
                        'comprovativo' => $pedido_reembolso->comprovativo,
                        'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                        'nome_beneficiario' => empty($pedido_reembolso->beneficiario) ? '' : $pedido_reembolso->beneficiario->nome,
                        'nome_dependente' => empty($pedido_reembolso->dependenteBeneficiario) ? '' : $pedido_reembolso->dependenteBeneficiario->nome,
                    ];
                });
        } elseif (!empty($cliente->dependente_beneficiario_id)) {
            $pedidos_reembolso = $this->pedido_reembolso
                ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
                ->byDependenteBeneficiario($cliente->dependente_beneficiario_id)
                ->where('estado_pedido_reembolso_id', $estado_pedido_reembolso->id)
                ->get(['id', 'unidade_sanitaria', 'servico_prestado', 'nr_comprovativo', 'valor', 'data', 'comentario', 'comprovativo', 'beneficio_proprio_beneficiario', 'beneficiario_id', 'dependente_beneficiario_id', 'estado_pedido_reembolso_id'])
                ->map(function ($pedido_reembolso) {
                    $data = date('Y-m-d H:i:s', strtotime($pedido_reembolso->data));

                    return [
                        'id' => $pedido_reembolso->id,
                        'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                        'servico_prestado' => $pedido_reembolso->servico_prestado,
                        'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                        'valor' => $pedido_reembolso->valor,
                        'data' => $pedido_reembolso->data,
                        'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                        'comentario' => $pedido_reembolso->comentario,
                        'comprovativo' => $pedido_reembolso->comprovativo,
                        'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                        'nome_beneficiario' => empty($pedido_reembolso->beneficiario) ? '' : $pedido_reembolso->beneficiario->nome,
                        'nome_dependente' => empty($pedido_reembolso->dependenteBeneficiario) ? '' : $pedido_reembolso->dependenteBeneficiario->nome,
                    ];
                });
        } else {
            return $this->sendError('Não foi informado o beneficiario_id ou o dependente_beneficiario_id!', 404);
        }

        return $this->sendResponse($pedidos_reembolso->toArray(), 'Pedidos de Reembolso devolvidos com sucesso!', 200);
    } */

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUpdatePedidoReembolsoFormRequest $request)
    {
        // dd($request->ficheiros);
        $cliente = Auth::user();
        // dd($cliente->beneficiario);
        $estado_aguardando_confirmacao = EstadoPedidoReembolso::where('codigo', '10')->first();

        if (!empty($request->ficheiros)) {
            if (!$this->validarFicheiros($request)) {
                return $this->sendError('Ficheiros inválidos!', 403);
            }
        }
        if ((empty($cliente->beneficiario)) || ($cliente->beneficiario->activo == false))
            return $this->sendError('O Cliente não está associado a nenhuma conta de Beneficiário ou a sua conta de Beneficiario encontra-se inactiva!', 404);

        if (empty($estado_aguardando_confirmacao))
            return $this->sendError('Estado do Pedido não identificado! Contacte o Administrador.', 404);

        $input = $request->validated();

        $objecto_responsavel_array = [
            'nome' => 'Cliente: ' . $cliente->nome,
            'accao' => 'Submeteu',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $input['beneficiario_id'] = $cliente->beneficiario_id;
        $input['dependente_beneficiario_id'] = null;
        $input['estado_pedido_reembolso_id'] = $estado_aguardando_confirmacao->id;
        $input['comprovativo'] = [];
        $input['comentario'] = [];
        $input['responsavel'] = [$objecto_responsavel_array];
        $temp_nome_ficheiros = [];


        if (!$request->beneficio_proprio_beneficiario) {
            $input['dependente_beneficiario_id'] = $request->dependente_beneficiario_id;
        }

        if (!empty($request->comentario)) {

            $objecto_comentario_array = [
                'proveniencia' => 'Mobile',
                'nome' => $cliente->nome,
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
                                return $this->sendError('Não foram removidos todos ficheiros! ');
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
            $responsavel = $pedido_reembolso->responsavel;
            $comentario = $pedido_reembolso->comentario;

            if (is_array($responsavel))
                usort($responsavel, sort_desc_array_objects('data'));

            if (is_array($comentario))
                usort($comentario, sort_desc_array_objects('data'));

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
                'comprovativo' => $pedido_reembolso->comprovativo,
                'comprovativo_link' => $pedido_reembolso->comprovativo_link,
                'comentario' => $comentario,
                'responsavel' => $responsavel,
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
                    return $this->sendError('Não foram removidos todos ficheiros! ');
                }
            }

            return $this->sendError($e->getTrace());
        }
    }



    public function resubmeterPedidoReembolso(CreateUpdatePedidoReembolsoFormRequest $request, $id)
    {
        // dd($request->ficheiros);
        $cliente = Auth::user();
        // dd($cliente->beneficiario);
        $estado_aguardando_confirmacao = EstadoPedidoReembolso::where('codigo', '10')->first();

        if (!empty($request->ficheiros)) {
            if (!$this->validarFicheiros($request)) {
                return $this->sendError('Ficheiros inválidos!', 403);
            }
        }
        if ((empty($cliente->beneficiario)) || ($cliente->beneficiario->activo == false))
            return $this->sendError('O Cliente não está associado a nenhuma conta de Beneficiário ou a sua conta de Beneficiario encontra-se inactiva!', 404);

        if (empty($estado_aguardando_confirmacao))
            return $this->sendError('Estado do Pedido não identificado! Contacte o Administrador.', 404);



        $input = $request->validated();

        $objecto_responsavel_array = [
            'nome' => 'Cliente: ' . $cliente->nome,
            'accao' => 'Re-Submeteu',
            'data' => date('d-m-Y H:i:s', strtotime(now()))
        ];
        $input['beneficiario_id'] = $cliente->beneficiario_id;
        $input['dependente_beneficiario_id'] = null;
        $input['estado_pedido_reembolso_id'] = $estado_aguardando_confirmacao->id;
        // $input['comprovativo'] = [];
        $temp_nome_ficheiros = [];

        if (!$request->beneficio_proprio_beneficiario) {
            $input['dependente_beneficiario_id'] = $request->dependente_beneficiario_id;
        }



        $pedido_reembolso = $this->pedido_reembolso
            ->byBeneficiario($cliente->beneficiario->id)
            ->with('empresa')
            ->find($id);

        if (empty($pedido_reembolso))
            return $this->sendError('Pedido de Reembolso não encontrado.', 404);


        if (!empty($request->comentario)) {

            $objecto_comentario_array = [
                'proveniencia' => 'Mobile',
                'nome' => $cliente->nome,
                'data' => date('d-m-Y H:i:s', strtotime(now())),
                'comentario' => $request->comentario
            ];

            if (!empty($pedido_reembolso->comentario)) {
                $comentario = $pedido_reembolso->comentario;
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

        DB::beginTransaction();
        try {

            $pedido_reembolso->update($input);

            // $path = kebab_case($pedido_reembolso->empresa->nome) . '-' . $pedido_reembolso->empresa->id . '/pedidos-reembolso/' . $pedido_reembolso->id . '/';
            $path = storage_path_empresa($pedido_reembolso->empresa->nome, $pedido_reembolso->empresa->id, 'pedidos-reembolso') . "$pedido_reembolso->id/";

            if (!empty($request->ficheiros)) {
                foreach ($request->ficheiros as $ficheiro) {

                    $upload = upload_file_s3($path, $ficheiro);

                    if (empty($upload)) {
                        DB::rollback();
                        if (!empty($path)) {
                            if (!$this->apagarFicheiros($path, $temp_nome_ficheiros)) {
                                return $this->sendError('Não foram removidos todos ficheiros! ');
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
            $responsavel = $pedido_reembolso->responsavel;
            $comentario = $pedido_reembolso->comentario;

            if (is_array($responsavel))
                usort($responsavel, sort_desc_array_objects('data'));

            if (is_array($comentario))
                usort($comentario, sort_desc_array_objects('data'));

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
                'comprovativo' => $pedido_reembolso->comprovativo,
                'comprovativo_link' => $pedido_reembolso->comprovativo_link,
                'comentario' => $comentario,
                'responsavel' => $responsavel,
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
                    return $this->sendError('Não foram removidos todos ficheiros! ');
                }
            }

            return $this->sendError($e->getTrace());
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
        $beneficiario = Auth::user()->beneficiario;
        $data = null;

        if (!empty($beneficiario)) {
            $pedido_reembolso = $this->pedido_reembolso
                ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
                ->byBeneficiario($beneficiario->id)
                ->where('id', $id)
                ->first([
                    'id',
                    'unidade_sanitaria',
                    'servico_prestado',
                    'nr_comprovativo',
                    'valor',
                    'data',
                    'comentario',
                    'responsavel',
                    'comprovativo',
                    'beneficio_proprio_beneficiario',
                    'beneficiario_id',
                    'dependente_beneficiario_id',
                    'estado_pedido_reembolso_id'
                ]);

            if (!empty($pedido_reembolso)) {

                $responsavel = $pedido_reembolso->responsavel;
                $comentario = $pedido_reembolso->comentario;

                if (is_array($responsavel))
                    usort($responsavel, sort_desc_array_objects('data'));

                if (is_array($comentario))
                    usort($comentario, sort_desc_array_objects('data'));

                $data = [
                    'id' => $pedido_reembolso->id,
                    'unidade_sanitaria' => $pedido_reembolso->unidade_sanitaria,
                    'servico_prestado' => $pedido_reembolso->servico_prestado,
                    'nr_comprovativo' => $pedido_reembolso->nr_comprovativo,
                    'valor' => $pedido_reembolso->valor,
                    'data' => $pedido_reembolso->data,
                    'estado_pedido_reembolso_codigo' => empty($pedido_reembolso->estadoPedidoReembolso) ? '' : $pedido_reembolso->estadoPedidoReembolso->codigo,
                    'comentario' => $comentario,
                    'responsavel' => $responsavel,
                    'comprovativo' => $pedido_reembolso->comprovativo,
                    'beneficio_proprio_beneficiario' => $pedido_reembolso->beneficio_proprio_beneficiario,
                    'nome_beneficiario' => empty($pedido_reembolso->beneficiario) ? '' : $pedido_reembolso->beneficiario->nome,
                    'nome_dependente' => empty($pedido_reembolso->dependenteBeneficiario) ? '' : $pedido_reembolso->dependenteBeneficiario->nome,
                ];
            } else {
                return $this->sendError('Pedido de Reembolso não encontrado', 404);
            }

            return $this->sendResponse($data, '', 200);
        } else {
            return $this->sendError('Beneficiário não encontrado, ou o cliente não possui uma conta de beneficiário!', 404);
        }
    }


    public function getGastoPedidoReembolso()
    {
        $gastos_reembolso = $this->gasto_reembolso->orderBy('nome', 'ASC')->get(['nome']);

        $data = [
            'gastos_reemboslo' => $gastos_reembolso,
        ];

        return $this->sendResponse($data, '', 200);
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

    public function removerFicheiro($id, $ficheiro)
    {
        $cliente = Auth::user();
        $beneficiario = null;
        // dd($cliente);


        if ((empty($cliente->beneficiario)) || ($cliente->beneficiario->activo == false)) {
            return $this->sendError('O Cliente não está associado a nenhuma conta de Beneficiário ou a sua conta de Beneficiario encontra-se inactiva!', 404);
        } else {
            $beneficiario = $cliente->beneficiario;
        }

        $pedido_reembolso = $this->pedido_reembolso
            ->byBeneficiario($beneficiario->id)
            ->with(['empresa'])
            ->find($id);

        if (empty($pedido_reembolso))
            return $this->sendError('Pedido de Reembolso não encontrado.', 404);

        DB::beginTransaction();
        try {

            if (!empty($pedido_reembolso->empresa)) {
                // $path = kebab_case($pedido_reembolso->empresa->nome) . '-' . $pedido_reembolso->empresa->id . '/pedidos-reembolso/' . $pedido_reembolso->id . '/';
                $path = storage_path_empresa($pedido_reembolso->empresa->nome, $pedido_reembolso->empresa->id, 'pedidos-reembolso') . "$pedido_reembolso->id/";
            } else {
                return $this->sendError('Caminho do ficheiro não encontrado pela falta de uma Empresa associada.', 404);
            }

            $comprovativo = $pedido_reembolso->comprovativo;

            if (is_array($comprovativo)) {
                if (($key = array_search($ficheiro, $comprovativo)) !== false) {
                    array_splice($comprovativo, $key, 1);

                    $pedido_reembolso->comprovativo = $comprovativo;
                    $pedido_reembolso->save();
                }

                if (!$this->apagarFicheiros($path, $ficheiro)) {
                    return $this->sendError('Não foi removido o ficheiro.', 500);
                }

                DB::commit();
                return $this->sendSuccess('Ficheiro removido com sucesso.', 200);
            } else if (is_null($comprovativo)) {
                return $this->sendError('O Pedido de Reembolso não possui ficheiros por remover.', 404);
            } else {
                return $this->sendError('Ficheiros em formato inválido na Base de Dados.', 404);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('O ficheiro não foi removido!' . $e->getMessage(), 500);
        }
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
}
