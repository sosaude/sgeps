<?php

namespace App\Http\Controllers\API\Empresa;

use Exception;
use App\Models\Pais;
use App\Models\Continente;
use App\Models\PlanoSaude;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use App\Mail\SendPlanoSaudeMail;
use App\Models\CategoriaServico;
use App\Models\GrupoMedicamento;
use App\Models\GrupoBeneficiario;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Models\CategoriaServicoPlano;
use App\Models\GrupoMedicamentoPlano;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Tenant\Empresa\CreateUpdatePlanoSaudeFormRequest;
use App\Http\Requests\API\Tenant\Empresa\CreateUpdatePlanoSaudePadaraoFormRequest;

class PlanoSaudeAPIController extends AppBaseController
{
    private $plano_saude;
    private $continente;
    private $grupo_beneficiario;
    private $grupo_medicamento;
    private $grupo_medicamento_plano;
    private $categoria_servico;
    private $categoria_servico_plano;


    public function __construct(
        PlanoSaude $plano_saude,
        Continente $continente,
        GrupoBeneficiario $grupo_beneficiario,
        GrupoMedicamento $grupo_medicamento,
        GrupoMedicamentoPlano $grupo_medicamento_plano,
        CategoriaServico $categoria_servico,
        CategoriaServicoPlano $categoria_servico_plano
    ) {

        $this->plano_saude = $plano_saude;
        $this->continente = $continente;
        $this->grupo_beneficiario = $grupo_beneficiario;
        $this->grupo_medicamento = $grupo_medicamento;
        $this->grupo_medicamento_plano = $grupo_medicamento_plano;
        $this->categoria_servico = $categoria_servico;
        $this->categoria_servico_plano = $categoria_servico_plano;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('gerir plano de saúde')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');
        /* $planos_saude = $this->plano_saude
            ->with(
                'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,plano_saude_id,grupo_medicamento_id',
                'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                'gruposMedicamentoPlano.medicamentos:id,nome_generico_medicamento_id',
                'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome'
            )
            ->get(['id', 'limite_anual_segurando', 'valor_limite_anual_segurando', 'valor_limite_fora_area_cobertura', 'regiao_cobertura', 'grupo_beneficiario_id', 'empresa_id']); */

        $planos_saude = $this->plano_saude
            ->byEmpresa($empresa_id)
            ->with(
                'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id', // if leave id and not medicamento it is ambiguous
                'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome',

                'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                'categoriasServicoPlano.categoriaServico:id,nome',
                'categoriasServicoPlano.servicos:id,nome' // if leave id instead of id it is ambiguous
            )
            ->get(['id', 'beneficio_anual_segurando_limitado', 'valor_limite_anual_segurando', 'limite_fora_area_cobertura', 'valor_limite_fora_area_cobertura', 'regiao_cobertura', 'grupo_beneficiario_id', 'empresa_id'])
            ->map(function ($plano_saude) {

                $grupos_medicamento_plano = $plano_saude->gruposMedicamentoPlano->map(function ($grupo_medicamento_plano) {

                    // $medicamentos = $grupo_medicamento_plano->medicamentos->mapToGroups(function ($medicamento, $key) {
                    $medicamentos = $grupo_medicamento_plano->medicamentos->map(function ($medicamento) {
                        return [
                            'id' => $medicamento->id, // if leave id and not medicamento it is ambiguous
                            'sub_grupo_id' => $medicamento->subGrupoMedicamento->id,
                            'sub_grupo' => $medicamento->subGrupoMedicamento->nome,
                            'nome_generico' => $medicamento->nomeGenerico->nome,
                            'coberto' => $medicamento->pivot->coberto,
                            'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                        ];
                    });
                    // dd($grupo_medicamento_plano->medicamentos);
                    return [
                        'id' => $grupo_medicamento_plano->id,
                        'comparticipacao_factura' => $grupo_medicamento_plano->comparticipacao_factura,
                        'sujeito_limite_global' => $grupo_medicamento_plano->sujeito_limite_global,
                        'beneficio_ilimitado' => $grupo_medicamento_plano->beneficio_ilimitado,
                        'valor_beneficio_limitado' => $grupo_medicamento_plano->valor_beneficio_limitado,
                        'valor_comparticipacao_factura' => $grupo_medicamento_plano->valor_comparticipacao_factura,
                        'grupo_medicamento_id' => $grupo_medicamento_plano->grupo_medicamento_id,
                        'grupo_medicamento_nome' => !empty($grupo_medicamento_plano->grupoMedicamento) ? $grupo_medicamento_plano->grupoMedicamento->nome : '',
                        'medicamentos' => $medicamentos,
                    ];
                });

                $categorias_servico_plano = $plano_saude->categoriasServicoPlano->map(function ($categoria_servico_plano) {

                    $servicos = $categoria_servico_plano->servicos->map(function ($servico) {
                        return [
                            'id' => $servico->id, // if leave id instead of id it is ambiguous
                            'nome' => $servico->nome,
                            'coberto' => $servico->pivot->coberto,
                            'pre_autorizacao' => $servico->pivot->pre_autorizacao,
                        ];
                    });

                    return [
                        'id' => $categoria_servico_plano->id,
                        'comparticipacao_factura' => $categoria_servico_plano->comparticipacao_factura,
                        'sujeito_limite_global' => $categoria_servico_plano->sujeito_limite_global,
                        'beneficio_ilimitado' => $categoria_servico_plano->beneficio_ilimitado,
                        'valor_beneficio_limitado' => $categoria_servico_plano->valor_beneficio_limitado,
                        'valor_comparticipacao_factura' => $categoria_servico_plano->valor_comparticipacao_factura,
                        'categoria_servico_id' => $categoria_servico_plano->categoria_servico_id,
                        'categoria_servico_nome' => !empty($categoria_servico_plano->categoriaServico) ? $categoria_servico_plano->categoriaServico->nome : '',
                        'servicos' => $servicos,
                    ];
                });


                return [
                    'id' => $plano_saude->id,
                    'beneficio_anual_segurando_limitado' => $plano_saude->beneficio_anual_segurando_limitado,
                    'valor_limite_anual_segurando' => $plano_saude->valor_limite_anual_segurando,
                    'limite_fora_area_cobertura' => $plano_saude->limite_fora_area_cobertura,
                    'valor_limite_fora_area_cobertura' => $plano_saude->valor_limite_fora_area_cobertura,
                    'regiao_cobertura' => $plano_saude->regiao_cobertura,
                    'grupo_beneficiario_id' => $plano_saude->grupo_beneficiario_id,
                    'grupos_medicamento_plano' => $grupos_medicamento_plano,
                    'categorias_servico_plano' => $categorias_servico_plano,
                ];
            });

        return $this->sendResponse($planos_saude->toArray(), 'Planos Saude retrieved successfully!', 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Gate::denies('gerir plano de saúde')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $continentes = $this->continente->with('paises:id,nome,codigo,continente_id')->get();
        $grupos_medicamentos = $this->grupo_medicamento
            ->with(
                'subGruposMedicamentos:id,nome,grupo_medicamento_id',
                'subGruposMedicamentos.medicamentos.nomeGenerico:id,nome',
                'subGruposMedicamentos.medicamentos.formaMedicamento:id,forma',
                'subGruposMedicamentos.medicamentos:id,codigo,dosagem,nome_generico_medicamento_id,forma_medicamento_id,sub_grupo_medicamento_id'

            )
            ->get()
            ->map(function ($grupo_medicamentos) {
                $sub_grupos_medicamentos = $grupo_medicamentos->subGruposMedicamentos->map(function ($sub_grupo_medicamentos) {
                    $medicamentos = $sub_grupo_medicamentos->medicamentos->map(function ($medicamento) {
                        return [
                            'id' => $medicamento->id,
                            'codigo' => $medicamento->codigo,
                            'dosagem' => $medicamento->dosagem,
                            'nome_generico' => $medicamento->nomeGenerico->nome,
                            'forma' => $medicamento->formaMedicamento->forma,
                        ];;
                    });


                    return [
                        'id' => $sub_grupo_medicamentos->id,
                        'nome' => $sub_grupo_medicamentos->nome,
                        'medicamentos' => $medicamentos,
                    ];
                });

                return [
                    'id' => $grupo_medicamentos->id,
                    'nome' => $grupo_medicamentos->nome,
                    'sub_grupos_medicamentos' => $sub_grupos_medicamentos,
                ];
            });

        $categorias_sevicos = $this->categoria_servico
            ->with('servicos:id,nome,categoria_servico_id')
            ->get(['id', 'nome'])
            ->map(function ($categoria_servico) {
                $servicos = $categoria_servico->servicos->map(function ($servico) {
                    return [
                        'id' => $servico->id,
                        'nome' => $servico->nome,
                    ];
                });

                return [
                    'id' => $categoria_servico->id,
                    'nome' => $categoria_servico->nome,
                    'servicos' => $servicos
                ];
            });


        $data = [
            'categorias_servicos' => $categorias_sevicos,
            'grupos_medicamentos' => $grupos_medicamentos,
            'continentes' => $continentes
        ];

        return $this->sendResponse($data, 'Resources retrieved successfully!', 200);
    }

    public function getPlanoSaudePadrao()
    {
        if (Gate::denies('gerir plano de saúde')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');
        $data = [];

        $plano_saude = $this->plano_saude
            ->byEmpresa($empresa_id)
            ->where('grupo_beneficiario_id', null)
            // ->orWhere('grupo_beneficiario_id', null)
            ->with(
                'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id',
                'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome',

                'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                'categoriasServicoPlano.categoriaServico:id,nome',
                'categoriasServicoPlano.servicos:id,nome'
            )
            ->first();

        if (empty($plano_saude)) {
            $data = [
                'padrao' => true,
                'plano_saude' => $plano_saude
            ];
            return $this->sendResponse($data, '', 200);
        }

        $grupos_medicamento_plano = $plano_saude->gruposMedicamentoPlano->map(function ($grupo_medicamento_plano) {

            $medicamentos = $grupo_medicamento_plano->medicamentos->map(function ($medicamento) {
                return [
                    'id' => $medicamento->id,
                    'sub_grupo_id' => $medicamento->subGrupoMedicamento->id,
                    'sub_grupo' => $medicamento->subGrupoMedicamento->nome,
                    'nome_generico' => $medicamento->nomeGenerico->nome,
                    'coberto' => $medicamento->pivot->coberto,
                    'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                ];
            });

            return [
                'id' => $grupo_medicamento_plano->id,
                'comparticipacao_factura' => $grupo_medicamento_plano->comparticipacao_factura,
                'sujeito_limite_global' => $grupo_medicamento_plano->sujeito_limite_global,
                'beneficio_ilimitado' => $grupo_medicamento_plano->beneficio_ilimitado,
                'valor_beneficio_limitado' => $grupo_medicamento_plano->valor_beneficio_limitado,
                'valor_comparticipacao_factura' => $grupo_medicamento_plano->valor_comparticipacao_factura,
                'grupo_medicamento_id' => $grupo_medicamento_plano->grupo_medicamento_id,
                'grupo_medicamento_nome' => !empty($grupo_medicamento_plano->grupoMedicamento) ? $grupo_medicamento_plano->grupoMedicamento->nome : '',
                'medicamentos' => $medicamentos,
            ];
        });

        $categorias_servico_plano = $plano_saude->categoriasServicoPlano->map(function ($categoria_servico_plano) {
            $servicos = $categoria_servico_plano->servicos->map(function ($servico) {
                return [
                    'id' => $servico->id,
                    'nome' => $servico->nome,
                    'coberto' => $servico->pivot->coberto,
                    'pre_autorizacao' => $servico->pivot->pre_autorizacao,
                ];
            });

            return [
                'id' => $categoria_servico_plano->id,
                'comparticipacao_factura' => $categoria_servico_plano->comparticipacao_factura,
                'sujeito_limite_global' => $categoria_servico_plano->sujeito_limite_global,
                'beneficio_ilimitado' => $categoria_servico_plano->beneficio_ilimitado,
                'valor_beneficio_limitado' => $categoria_servico_plano->valor_beneficio_limitado,
                'valor_comparticipacao_factura' => $categoria_servico_plano->valor_comparticipacao_factura,
                'categoria_servico_id' => empty($categoria_servico_plano->categoriaServico) ? '' : $categoria_servico_plano->categoriaServico->id,
                'categoria_servico_nome' => empty($categoria_servico_plano->categoriaServico) ? '' : $categoria_servico_plano->categoriaServico->nome,
                'servicos' => $servicos,
            ];
        });

        $plano_saude = [
            'id' => $plano_saude->id,
            'beneficio_anual_segurando_limitado' => $plano_saude->beneficio_anual_segurando_limitado,
            'valor_limite_anual_segurando' => $plano_saude->valor_limite_anual_segurando,
            'limite_fora_area_cobertura' => $plano_saude->limite_fora_area_cobertura,
            'valor_limite_fora_area_cobertura' => $plano_saude->valor_limite_fora_area_cobertura,
            'regiao_cobertura' => $plano_saude->regiao_cobertura,
            'grupo_beneficiario_id' => $plano_saude->grupo_beneficiario_id,
            'grupos_medicamento_plano' => $grupos_medicamento_plano,
            'categorias_servico_plano' => $categorias_servico_plano,
        ];


        $data = [
            'padrao' => true,
            'plano_saude' => $plano_saude
        ];

        return $this->sendResponse($data, 'Plano Saúde padrão retrieved succeessfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request or CreateUpdatePlanoSaudeFormRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    { }
    public function setPlanoSaudePadrao(CreateUpdatePlanoSaudeFormRequest $request)
    {
        // dd('QUanto tempo');
        if (Gate::denies('gerir plano de saúde')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // dd($request->categorias_servico_plano);
        $input_plano_saude = $request->only(['beneficio_anual_segurando_limitado', 'valor_limite_anual_segurando', 'limite_fora_area_cobertura', 'valor_limite_fora_area_cobertura', 'regiao_cobertura', 'grupo_beneficiario_id', 'empresa_id']);
        $input_grupos_medicamento_plano = $request->grupos_medicamento_plano;
        $input_categorias_servico_plano = $request->categorias_servico_plano;
        $plano_saude_id = $request->plano_saude_id;
        $plano_saude = null;
        $empresa_id = $request->empresa_id;

        if (isset($plano_saude_id)) {
            $plano_saude = $this->plano_saude
                ->byEmpresa($empresa_id)
                ->find($plano_saude_id);

            if (empty($plano_saude)) {
                return $this->sendError('Plano de Saúde not found', 404);
            }
        }
        // dd($plano_saude);
        DB::beginTransaction();
        try {

            // Se for uma configuração nova para o grupo(não existe plano para este grupo) é feito o create do plano, caso seja um grupo que já possui o plano, faz-se a actualização do plano
            if (empty($plano_saude)) {
                $plano_saude = $this->savePlanoSaude($input_plano_saude, $input_grupos_medicamento_plano, $input_categorias_servico_plano);
            } else {
                $plano_saude = $this->updatePlanoSaude($plano_saude, $input_plano_saude, $input_grupos_medicamento_plano, $input_categorias_servico_plano);
            }

            if (!empty($plano_saude->grupo_beneficiario_id)) {
                $emails_beneficiarios = Beneficiario::where('email', '!=', null)->where('grupo_beneficiario_id', $plano_saude->grupo_beneficiario_id)->pluck('email');
                $when = now()->addSeconds(10);
    
                foreach ($emails_beneficiarios as $key => $email) {
                    Mail::to($email)->later($when, new SendPlanoSaudeMail());
                }
            }
            
            DB::commit();
            return $this->sendResponse('Plano de saúde saved successfully!', []);
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
    { }


    public function getConfigurarPlanoSaude($grupo_beneficiario_id)
    {
        if (Gate::denies('gerir plano de saúde')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');
        $padrao = false;

        $grupo_beneficiario = $this->grupo_beneficiario->byEmpresa($empresa_id)->find($grupo_beneficiario_id);
        if (empty($grupo_beneficiario))
            return $this->sendError('Grupo Beneficiário não encontrado!');

        $plano_saude = $this->plano_saude
            ->byEmpresa($empresa_id)
            ->where('grupo_beneficiario_id', $grupo_beneficiario_id)
            ->with(
                'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id',
                'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome',

                'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                'categoriasServicoPlano.categoriaServico:id,nome',
                'categoriasServicoPlano.servicos:id,nome'
            )
            ->first();

        if (empty($plano_saude)) {

            $plano_saude = $this->plano_saude
                ->byEmpresa($empresa_id)
                ->where('grupo_beneficiario_id', null)
                // ->orWhere('grupo_beneficiario_id', null)
                ->with(
                    'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                    'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                    'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id',
                    'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                    'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome',

                    'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                    'categoriasServicoPlano.categoriaServico:id,nome',
                    'categoriasServicoPlano.servicos:id,nome'
                )
                ->first();

            if (empty($plano_saude)) {
                return $this->sendError('Plano de Saúde não encontrado');
            } else {
                $padrao = true;
            }
        }

        $grupos_medicamento_plano = $plano_saude->gruposMedicamentoPlano->map(function ($grupo_medicamento_plano) {

            $medicamentos = $grupo_medicamento_plano->medicamentos->map(function ($medicamento) {
                return [
                    'id' => $medicamento->id,
                    'sub_grupo_id' => $medicamento->subGrupoMedicamento->id,
                    'sub_grupo' => $medicamento->subGrupoMedicamento->nome,
                    'nome_generico' => $medicamento->nomeGenerico->nome,
                    'coberto' => $medicamento->pivot->coberto,
                    'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                ];
            });

            return [
                'id' => $grupo_medicamento_plano->id,
                'comparticipacao_factura' => $grupo_medicamento_plano->comparticipacao_factura,
                'sujeito_limite_global' => $grupo_medicamento_plano->sujeito_limite_global,
                'beneficio_ilimitado' => $grupo_medicamento_plano->beneficio_ilimitado,
                'valor_beneficio_limitado' => $grupo_medicamento_plano->valor_beneficio_limitado,
                'valor_comparticipacao_factura' => $grupo_medicamento_plano->valor_comparticipacao_factura,
                'grupo_medicamento_id' => $grupo_medicamento_plano->grupo_medicamento_id,
                'grupo_medicamento_nome' => !empty($grupo_medicamento_plano->grupoMedicamento) ? $grupo_medicamento_plano->grupoMedicamento->nome : '',
                'medicamentos' => $medicamentos,
            ];
        });

        $categorias_servico_plano = $plano_saude->categoriasServicoPlano->map(function ($categoria_servico_plano) {
            $servicos = $categoria_servico_plano->servicos->map(function ($servico) {
                return [
                    'id' => $servico->id,
                    'nome' => $servico->nome,
                    'coberto' => $servico->pivot->coberto,
                    'pre_autorizacao' => $servico->pivot->pre_autorizacao,
                ];
            });

            return [
                'id' => $categoria_servico_plano->id,
                'comparticipacao_factura' => $categoria_servico_plano->comparticipacao_factura,
                'sujeito_limite_global' => $categoria_servico_plano->sujeito_limite_global,
                'beneficio_ilimitado' => $categoria_servico_plano->beneficio_ilimitado,
                'valor_beneficio_limitado' => $categoria_servico_plano->valor_beneficio_limitado,
                'valor_comparticipacao_factura' => $categoria_servico_plano->valor_comparticipacao_factura,
                'categoria_servico_id' => empty($categoria_servico_plano->categoriaServico) ? '' : $categoria_servico_plano->categoriaServico->id,
                'categoria_servico_nome' => empty($categoria_servico_plano->categoriaServico) ? '' : $categoria_servico_plano->categoriaServico->nome,
                'servicos' => $servicos,
            ];
        });

        $plano_saude = [
            'id' => $plano_saude->id,
            'beneficio_anual_segurando_limitado' => $plano_saude->beneficio_anual_segurando_limitado,
            'valor_limite_anual_segurando' => $plano_saude->valor_limite_anual_segurando,
            'limite_fora_area_cobertura' => $plano_saude->limite_fora_area_cobertura,
            'valor_limite_fora_area_cobertura' => $plano_saude->valor_limite_fora_area_cobertura,
            'regiao_cobertura' => $plano_saude->regiao_cobertura,
            'grupo_beneficiario_id' => $plano_saude->grupo_beneficiario_id,
            'grupos_medicamento_plano' => $grupos_medicamento_plano,
            'categorias_servico_plano' => $categorias_servico_plano,
        ];


        // RESOURCES FOR EDITING



        $data = [
            'padrao' => $padrao,
            'plano_saude' => $plano_saude,
        ];

        return $this->sendResponse($data, '', 200);
    }



    public function setConfigurarPlanoSaude(CreateUpdatePlanoSaudeFormRequest $request)
    {
        if (Gate::denies('gerir plano de saúde')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate(['grupo_beneficiario_id' => 'required|integer']);
        $empresa_id = $request->empresa_id;
        $grupo_beneficiario_id = $request->grupo_beneficiario_id;
        $plano_saude_id = $request->plano_saude_id;

        $input_plano_saude = $request->only(['beneficio_anual_segurando_limitado', 'valor_limite_anual_segurando', 'limite_fora_area_cobertura', 'valor_limite_fora_area_cobertura', 'regiao_cobertura', 'grupo_beneficiario_id', 'empresa_id']);
        $input_grupos_medicamento_plano = $request->grupos_medicamento_plano;
        $input_categorias_servico_plano = $request->categorias_servico_plano;
        // dd($input_grupos_medicamento_plano);

        // valida a combinação padrao e plano_saude_id. Se padrao==true então não é esperado o plano_saude_id. Se padrao==false, então é esperado o plano_saude_id.
        if ($request->padrao == true) {
            if (!isset($plano_saude_id)) {
                $plano_saude = $this->plano_saude
                    ->byEmpresa($empresa_id)
                    ->where('grupo_beneficiario_id', $grupo_beneficiario_id)
                    ->first();

                if (!empty($plano_saude)) {
                    return $this->sendError('Plano de Saúde já existente para este grupo!', 404);
                }
            } else {
                return $this->sendError('Foi enviado o plano de saúde com um identificador, sendo que o atributo padrao assumiu o valor verdadeiro!', 404);
            }
        } else {
            if (isset($plano_saude_id)) {

                $plano_saude = $this->plano_saude
                    ->byEmpresa($empresa_id)
                    ->where('id', $plano_saude_id)
                    ->where('grupo_beneficiario_id', $grupo_beneficiario_id)
                    ->first();

                if (empty($plano_saude)) {
                    return $this->sendError('Plano de Saúde não encontrado!', 404);
                }
            } else {
                return $this->sendError('Foi enviado o plano de saúde sem um identificador, sendo que o atributo padrao assumiu o valor falso!', 404);
            }
        }



        /* if (!isset($plano_saude_id)) {

            $plano_saude = $this->plano_saude
                ->byEmpresa($empresa_id)
                ->where('grupo_beneficiario_id', $grupo_beneficiario_id)
                ->first();

            if (!empty($plano_saude)) {
                return $this->sendError('Plano de Saúde já existente para este grupo!', 404);
            }
        } else {

            $plano_saude = $this->plano_saude
                ->byEmpresa($empresa_id)
                ->where('id', $plano_saude_id)
                ->where('grupo_beneficiario_id', $grupo_beneficiario_id)
                ->first();

            if (empty($plano_saude)) {
                return $this->sendError('Plano de Saúde não encontrado!', 404);
            }
        } */




        // dd($plano_saude);
        DB::beginTransaction();
        try {

            // Se for uma configuração nova para o grupo(não existe plano para este grupo) é feito o create do plano, caso seja um grupo que já possui o plano, faz-se a actualização do plano
            if (empty($plano_saude)) {
                $plano_saude = $this->savePlanoSaude($input_plano_saude, $input_grupos_medicamento_plano, $input_categorias_servico_plano);
            } else {
                $plano_saude = $this->updatePlanoSaude($plano_saude, $input_plano_saude, $input_grupos_medicamento_plano, $input_categorias_servico_plano);
            }

            if (!empty($plano_saude->grupo_beneficiario_id)) {
                $emails_beneficiarios = Beneficiario::where('email', '!=', null)->where('grupo_beneficiario_id', $plano_saude->grupo_beneficiario_id)->pluck('email');
                $when = now()->addSeconds(10);
    
                foreach ($emails_beneficiarios as $key => $email) {
                    Mail::to($email)->later($when, new SendPlanoSaudeMail());
                }
            }
            
            DB::commit();
            return $this->sendResponse('Plano de saúde saved successfully!', []);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getTrace());
        }
    }


    public function redefinirPlanoSaude(Request $request)
    {
        $request->validate(['grupo_beneficiario_id' => 'required|integer', 'plano_saude_id' => 'required|integer']);
        $empresa_id = $request->empresa_id;
        $grupo_beneficiario_id = $request->grupo_beneficiario_id;
        $plano_saude_id = $request->plano_saude_id;

        $plano_saude = $this->plano_saude
            ->byEmpresa($empresa_id)
            ->where('grupo_beneficiario_id', $grupo_beneficiario_id)
            ->where('id', $plano_saude_id)
            ->first();

        if (empty($plano_saude))
            return $this->sendError('Plano de Saude não enconrado!');

        $plano_saude_padrao = $this->plano_saude
            ->byEmpresa($empresa_id)
            ->where('grupo_beneficiario_id', null)
            ->with([
                'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id',
                'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome',

                'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                'categoriasServicoPlano.categoriaServico:id,nome',
                'categoriasServicoPlano.servicos:id,nome'
            ])
            ->first();

        if (empty($plano_saude_padrao))
            return $this->sendError('Plano de Saude Padrão não enconrado!');

        $regiao_cobertura_ids = [];
        foreach ($plano_saude_padrao->regiao_cobertura as $regiao_cobertura) {
            array_push($regiao_cobertura_ids, $regiao_cobertura['id']);
        }
        $input_plano_saude = [
            'beneficio_anual_segurando_limitado' => $plano_saude_padrao->beneficio_anual_segurando_limitado,
            'valor_limite_anual_segurando' => $plano_saude_padrao->valor_limite_anual_segurando,
            'limite_fora_area_cobertura' => $plano_saude_padrao->limite_fora_area_cobertura,
            'valor_limite_fora_area_cobertura' => $plano_saude_padrao->valor_limite_fora_area_cobertura,
            'regiao_cobertura' => $regiao_cobertura_ids,
            'grupo_beneficiario_id' => $grupo_beneficiario_id,
            'empresa_id' => $plano_saude_padrao->empresa_id,
        ];

        $input_grupos_medicamento_plano = $plano_saude_padrao->gruposMedicamentoPlano
            ->map(function ($grupo_medicamento_plano) {

                $medicamentos = $grupo_medicamento_plano->medicamentos->map(function ($medicamento) {
                    return [
                        'id' => $medicamento->id,
                        'sub_grupo_id' => $medicamento->subGrupoMedicamento->id,
                        'sub_grupo' => $medicamento->subGrupoMedicamento->nome,
                        'nome_generico' => $medicamento->nomeGenerico->nome,
                        'coberto' => $medicamento->pivot->coberto,
                        'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                    ];
                });

                return [
                    'comparticipacao_factura' => $grupo_medicamento_plano->comparticipacao_factura,
                    'valor_comparticipacao_factura' => $grupo_medicamento_plano->valor_comparticipacao_factura,
                    'sujeito_limite_global' => $grupo_medicamento_plano->sujeito_limite_global,
                    'beneficio_ilimitado' => $grupo_medicamento_plano->beneficio_ilimitado,
                    'valor_beneficio_limitado' => $grupo_medicamento_plano->valor_beneficio_limitado,
                    'grupo_medicamento_id' => $grupo_medicamento_plano->grupo_medicamento_id,
                    'medicamentos' => $medicamentos
                ];
            });

        $input_categorias_servico_plano = $plano_saude_padrao->categoriasServicoPlano
            ->map(function ($categoria_servico_plano) {

                $servicos = $categoria_servico_plano->servicos->map(function ($servico) {
                    return [
                        'id' => $servico->id,
                        'nome' => $servico->nome,
                        'coberto' => $servico->pivot->coberto,
                        'pre_autorizacao' => $servico->pivot->pre_autorizacao,
                    ];
                });

                return [
                    'comparticipacao_factura' => $categoria_servico_plano->comparticipacao_factura,
                    'sujeito_limite_global' => $categoria_servico_plano->sujeito_limite_global,
                    'beneficio_ilimitado' => $categoria_servico_plano->beneficio_ilimitado,
                    'valor_beneficio_limitado' => $categoria_servico_plano->valor_beneficio_limitado,
                    'valor_comparticipacao_factura' => $categoria_servico_plano->valor_comparticipacao_factura,
                    'categoria_servico_id' => $categoria_servico_plano->categoria_servico_id,
                    'servicos' => $servicos
                ];
            });

        DB::beginTransaction();
        try {

            if ($plano_saude->gruposMedicamentoPlano->isNotEmpty()) {
                foreach ($plano_saude->gruposMedicamentoPlano as $grupo_medicamento_plano) {

                    if ($grupo_medicamento_plano->medicamentos->isNotEmpty()) {
                        $grupo_medicamento_plano->medicamentos()->detach();
                    }

                    $grupo_medicamento_plano->delete();
                }
            }

            if ($plano_saude->categoriasServicoPlano->isNotEmpty()) {
                foreach ($plano_saude->categoriasServicoPlano as $categoria_servico_plano) {

                    if ($categoria_servico_plano->servicos->isNotEmpty()) {
                        $categoria_servico_plano->servicos()->detach();
                    }

                    $categoria_servico_plano->delete();
                }
            }


            $this->updatePlanoSaude($plano_saude, $input_plano_saude, $input_grupos_medicamento_plano, $input_categorias_servico_plano);
            DB::commit();
            return $this->sendResponse('', 'Plano de Saúde redefinido com sucesso', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), 404);
        }
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


    private function validarGrupoBeneficiarioDaEmpresaAoPlano($empresa_id)
    {
        $plano_saude = PlanoSaude::byEmpresa($empresa_id)
            ->where('grupo_beneficiario_id', '')
            ->orWhere('grupo_beneficiario_id', null)
            ->first();

        if (!empty($plano_saude)) {
            return true;
        }

        return false;
    }

    // Salva o plano de saude para o grupo $input_plano_saude['grupo_beneficirio_id'], uma vez que ainda não existe um plano para este grupo
    protected function savePlanoSaude($input_plano_saude, $input_grupos_medicamento_plano, $input_categorias_servico_plano)
    {
        $plano_saude = $this->plano_saude->create($input_plano_saude);

        // Persiste os grupos de medicamentos e os seus respectivos medicamentos
        foreach ($input_grupos_medicamento_plano as $input_grupo_medicamento_plano) {

            $medicamentos = [];
            $input_grupo_medicamento_plano['plano_saude_id'] = $plano_saude->id;

            foreach ($input_grupo_medicamento_plano['medicamentos'] as $medicamento) {
                $medicamentos_to_attach = [$medicamento['id'] => ['coberto' => $medicamento['coberto'], 'pre_autorizacao' => $medicamento['pre_autorizacao']]];
                $medicamentos = array_replace($medicamentos, $medicamentos_to_attach);
            }

            $grupo_medicamento_plano = $this->grupo_medicamento_plano
                ->byPlanoSaude($plano_saude->id)
                ->where('grupo_medicamento_id', $input_grupo_medicamento_plano['grupo_medicamento_id'])
                ->first();

            // Se já existir este grupo para o plano, então não grava este grupo
            if (!empty($grupo_medicamento_plano))
                continue;


            $grupo_medicamento_plano = GrupoMedicamentoPlano::create($input_grupo_medicamento_plano);
            $grupo_medicamento_plano->medicamentos()->attach($medicamentos);
            $medicamentos = [];
        }

        // Persiste as categorias dos serviços e os seus serviços
        foreach ($input_categorias_servico_plano as $input_categoria_servico_plano) {
            $servicos = [];
            $input_categoria_servico_plano['plano_saude_id'] = $plano_saude->id;

            foreach ($input_categoria_servico_plano['servicos'] as $servico) {
                $servicos_to_attach = [$servico['id'] => ['coberto' => $servico['coberto'], 'pre_autorizacao' => $servico['pre_autorizacao']]];
                $servicos = array_replace($servicos, $servicos_to_attach);
            }

            $categoria_servico_plano = $this->categoria_servico_plano
                ->byPlanoSaude($plano_saude->id)
                ->where('categoria_servico_id', $input_categoria_servico_plano['categoria_servico_id'])
                ->first();

            if (!empty($categoria_servico_plano))
                continue;

            $categoria_servico_plano = CategoriaServicoPlano::create($input_categoria_servico_plano);
            $categoria_servico_plano->servicos()->attach($servicos);
            $servicos = [];
        }

        return $plano_saude;
    }

    // Actualiza o plano de saude e os seus grupos de medicamento com os dados vindos do request
    protected function updatePlanoSaude($plano_saude, $input_plano_saude, $input_grupos_medicamento_plano, $input_categorias_servico_plano)
    {
        // dd($input_grupos_medicamento_plano);
        $plano_saude = $plano_saude->fill($input_plano_saude);
        $plano_saude->save();

        // Actualiza os grupos de medicamentos e os respectivos medicamentos
        foreach ($input_grupos_medicamento_plano as $input_grupo_medicamento_plano) {

            // Se do formulário vier o grupo_medicamento_plano_id e este grupo não existir para o plano de saude actual, então passa para a proxima iteração
            /* if (isset($input_grupo_medicamento_plano['id'])) {
                // dd('Existe id');
                $grupo_medicamento_plano = $this->grupo_medicamento_plano
                    ->byPlanoSaude($plano_saude->id)
                    ->find($input_grupo_medicamento_plano['id']);

                if (empty($grupo_medicamento_plano))
                    continue;
            } else {
                // dd('Não Existe id');
                // Bloco esle para: Se for um novo grupo_medicamento_plano_id
                $grupo_medicamento_plano = $this->grupo_medicamento_plano
                    ->byPlanoSaude($plano_saude->id)
                    ->where('grupo_medicamento_id', $input_grupo_medicamento_plano['grupo_medicamento_id'])
                    ->first();
                    // dd($grupo_medicamento_plano);
                // Se já existir este grupo para o plano, então não grava este como novo grupo
                if (!empty($grupo_medicamento_plano))
                    continue;

                $grupo_medicamento_plano = new GrupoMedicamentoPlano();
                $grupo_medicamento_plano->plano_saude_id = $plano_saude->id;
            } */

            $grupo_medicamento_plano = $this->grupo_medicamento_plano
                ->byPlanoSaude($plano_saude->id)
                ->where('grupo_medicamento_id', $input_grupo_medicamento_plano['grupo_medicamento_id'])
                ->first();
            // dd($grupo_medicamento_plano);
            // Se já existir este grupo para o plano, então não grava este como novo grupo
            if (empty($grupo_medicamento_plano)) {
                $grupo_medicamento_plano = new GrupoMedicamentoPlano();
            }


            $grupo_medicamento_plano->plano_saude_id = $plano_saude->id;




            $medicamentos = [];

            foreach ($input_grupo_medicamento_plano['medicamentos'] as $medicamento) {
                $medicamentos_to_attach = [$medicamento['id'] => ['coberto' => $medicamento['coberto'], 'pre_autorizacao' => $medicamento['pre_autorizacao']]];
                $medicamentos = array_replace($medicamentos, $medicamentos_to_attach);
            }
            // dd($grupo_medicamento_plano);
            $grupo_medicamento_plano->fill($input_grupo_medicamento_plano);
            $grupo_medicamento_plano->save();
            $grupo_medicamento_plano->medicamentos()->detach();
            $grupo_medicamento_plano->medicamentos()->attach($medicamentos);
            $medicamentos = [];
        }
        // dd('Fim Medicamentos');
        // Actualiza as categorias de serviços e os respectivos serviços
        foreach ($input_categorias_servico_plano as $input_categoria_servico_plano) {

            /* if (isset($input_categoria_servico_plano['id'])) {
                $categoria_servico_plano = $this->categoria_servico_plano
                    ->byPlanoSaude($plano_saude->id)
                    ->find($input_categoria_servico_plano['id']);

                if (empty($categoria_servico_plano))
                    continue;
            } else {
                $categoria_servico_plano = $this->categoria_servico_plano
                    ->byPlanoSaude($plano_saude->id)
                    ->where('categoria_servico_id', $input_categoria_servico_plano['categoria_servico_id'])
                    ->first();

                if (!empty($categoria_servico_plano))
                    continue;

                $categoria_servico_plano = new CategoriaServicoPlano();
                $categoria_servico_plano->plano_saude_id = $plano_saude->id;
                } */


            $categoria_servico_plano = $this->categoria_servico_plano
                ->byPlanoSaude($plano_saude->id)
                ->where('categoria_servico_id', $input_categoria_servico_plano['categoria_servico_id'])
                ->first();

            if (empty($categoria_servico_plano)) {
                $categoria_servico_plano = new CategoriaServicoPlano();
            }


            $categoria_servico_plano->plano_saude_id = $plano_saude->id;


            $servicos = [];

            foreach ($input_categoria_servico_plano['servicos'] as $servico) {
                $servicos_to_attach = [$servico['id'] => ['coberto' => $servico['coberto'], 'pre_autorizacao' => $servico['pre_autorizacao']]];
                $servicos = array_replace($servicos, $servicos_to_attach);
            }

            $categoria_servico_plano->fill($input_categoria_servico_plano);
            $categoria_servico_plano->save();
            $categoria_servico_plano->servicos()->detach();
            $categoria_servico_plano->servicos()->attach($servicos);
            $servicos = [];
        }

        return $plano_saude;
    }

    public function removerPlanoSaude($id)
    {
        $empresa_id = request('empresa_id');
        $plano_saude = $this->plano_saude
            ->byEmpresa($empresa_id)
            ->with([
                'gruposMedicamentoPlano',
                'gruposMedicamentoPlano.medicamentos',
                'categoriasServicoPlano',
                'categoriasServicoPlano.servicos'
            ])
            ->find($id);

        if (empty($plano_saude))
            return $this->sendError('Plano Saúde nao encontrado.', 404);

        DB::beginTransaction();
        try {


            if ($plano_saude->gruposMedicamentoPlano->isNotEmpty()) {
                foreach ($plano_saude->gruposMedicamentoPlano as $grupo_medicamento_plano) {

                    if ($grupo_medicamento_plano->medicamentos->isNotEmpty()) {
                        $grupo_medicamento_plano->medicamentos()->detach();
                    }

                    $grupo_medicamento_plano->delete();
                }
            }

            if ($plano_saude->categoriasServicoPlano->isNotEmpty()) {
                foreach ($plano_saude->categoriasServicoPlano as $categoria_servico_plano) {

                    if ($categoria_servico_plano->servicos->isNotEmpty()) {
                        $categoria_servico_plano->servicos()->detach();
                    }

                    $categoria_servico_plano->delete();
                }
            }

            $plano_saude->delete();

            DB::commit();
            return $this->sendSuccess('Plano de Saúde removido com sucesso.', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage, 404);
        } catch (QueryException $e) {
            DB::rollback();
            return $this->sendError("Ocorreu um erro de integridade referêncial na Base de Dados, não pode remover este item. Contacte o Administrador!" . $e->getMessage(), 404);
        }
    }
}
