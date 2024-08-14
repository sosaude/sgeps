<?php

namespace App\Http\Controllers\API\Mobile;

use App\Models\Empresa;
use App\Models\Farmacia;
use App\Models\Medicamento;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use App\Models\UnidadeSanitaria;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AppBaseController;
use App\Models\BaixaFarmacia;
use App\Models\BaixaUnidadeSanitaria;
use App\Models\CategoriaServico;
use App\Models\GrupoBeneficiario;
use App\Models\PedidoReembolso;
use App\Models\PlanoSaude;
use App\Models\StockFarmacia;

class MobileAPIController extends AppBaseController
{
    //
    private $farmacia;
    private $baixa_farmacia;
    private $medicamento;
    private $unidade_sanitaria;
    private $baixa_unidade_sanitaria;
    private $categoria_servico;
    private $plano_saude;
    private $stock_farmacia;
    private $empresa;
    private $pedido_reembolso;

    public function __construct(UnidadeSanitaria $unidade_sanitaria, BaixaUnidadeSanitaria $baixa_unidade_sanitaria, Farmacia $farmacia, Medicamento $medicamento, BaixaFarmacia $baixa_farmacia, PlanoSaude $plano_saude, CategoriaServico $categoria_servico, StockFarmacia $stock_farmacia, Empresa $empresa, PedidoReembolso $pedido_reembolso)
    {
        $this->farmacia = $farmacia;
        $this->medicamento = $medicamento;
        $this->stock_farmacia = $stock_farmacia;
        $this->baixa_farmacia = $baixa_farmacia;
        $this->unidade_sanitaria = $unidade_sanitaria;
        $this->baixa_unidade_sanitaria = $baixa_unidade_sanitaria;
        $this->plano_saude = $plano_saude;
        $this->categoria_servico = $categoria_servico;
        $this->empresa = $empresa;
        $this->pedido_reembolso = $pedido_reembolso;
    }

    public function mapaUnidadesSanitarias()
    {
        $farmacia_ids = [];
        $unidade_sanitaria_ids = [];
        $beneficiario = null;
        $cliente = Auth::user();
        $cliente->load(['beneficiario:id,empresa_id', 'dependenteBeneficiario:id,beneficiario_id', 'dependenteBeneficiario.beneficiario:id,empresa_id']);


        if (!empty($cliente->beneficiario) && !empty($cliente->dependenteBeneficiario)) {
            return $this->sendError('Este cliente encontra-se associado à uma conta de Beneficiário e à uma conta de Dependente. Contacte o Administrador', 400);
        } else if (!empty($cliente->beneficiario)) {
            $beneficiario = $cliente->beneficiario;
        } else if (!empty($dependente_beneficiario = $cliente->dependenteBeneficiario)) {

            if (!empty($dependente_beneficiario->beneficiario)) {
                $beneficiario = $dependente_beneficiario->beneficiario;
            }
        }

        if (!empty($beneficiario)) {

            $farmacia_ids = $this->farmaciasDoConvenio($beneficiario);
            $unidade_sanitaria_ids = $this->unidadesSanitariasDoConvenio($beneficiario);
        }

        $farmacias = $this->farmacia
            ->get(['id', 'nome', 'endereco', 'horario_funcionamento', 'activa', 'contactos', 'latitude', 'longitude'])
            ->map(function ($farmacia) use ($farmacia_ids) {
                return [
                    'id' => $farmacia->id,
                    'nome' => $farmacia->nome,
                    'endereco' => $farmacia->endereco,
                    'horario_funcionamento' => $farmacia->horario_funcionamento,
                    'activa' => $farmacia->activa,
                    'contactos' => $farmacia->contactos,
                    'latitude' => $farmacia->latitude,
                    'longitude' => $farmacia->longitude,
                    'convenio' => in_array($farmacia->id, $farmacia_ids) ? true : false
                ];
            });

        $unidades_sanitarias = $this->unidade_sanitaria
            ->with('categoriaUnidadeSanitaria:id,codigo,nome')
            ->get(['id', 'nome', 'endereco', 'email', 'contactos', 'latitude', 'longitude', 'categoria_unidade_sanitaria_id'])
            ->map(function ($unidade_sanitaria) use ($unidade_sanitaria_ids) {
                return [
                    'id' => $unidade_sanitaria->id,
                    'nome'  => $unidade_sanitaria->nome,
                    'endereco'  => $unidade_sanitaria->endereco,
                    'email' => $unidade_sanitaria->email,
                    'contactos'  => $unidade_sanitaria->contactos,
                    'latitude'  => $unidade_sanitaria->latitude,
                    'longitude'  => $unidade_sanitaria->longitude,
                    'categoria_unidade_codigo' => $unidade_sanitaria->categoriaUnidadeSanitaria ? $unidade_sanitaria->categoriaUnidadeSanitaria->codigo : '',
                    'categoria_unidade_nome' => $unidade_sanitaria->categoriaUnidadeSanitaria ? $unidade_sanitaria->categoriaUnidadeSanitaria->nome : '',
                    'convenio' => in_array($unidade_sanitaria->id, $unidade_sanitaria_ids) ? true : false,
                ];
            });

        $data = [
            'unidades_sanitarias' => $unidades_sanitarias,
            'farmacias' => $farmacias
        ];

        return $this->sendResponse($data, '', 200);
    }

    public function pesquisaGeral($filtro = null)
    {
        $farmacias = $this->farmacia->where('nome', 'LIKE', '%' . $filtro . '%')->get(['id', 'nome', 'endereco', 'horario_funcionamento', 'activa', 'contactos', 'latitude', 'longitude']);

        $unidades_sanitarias = $this->unidade_sanitaria
            ->with('categoriaUnidadeSanitaria:id,codigo,nome')
            ->where('nome', 'LIKE', '%' . $filtro . '%')
            ->get(['id', 'nome', 'endereco', 'email', 'contactos', 'categoria_unidade_sanitaria_id', 'latitude', 'longitude'])
            ->map(function ($unidade_sanitaria) {
                return [
                    'id' => $unidade_sanitaria->id,
                    'nome'  => $unidade_sanitaria->nome,
                    'endereco'  => $unidade_sanitaria->endereco,
                    'email' => $unidade_sanitaria->email,
                    'contactos'  => $unidade_sanitaria->contactos,
                    'categoria_unidade_codigo' => $unidade_sanitaria->categoriaUnidadeSanitaria ? $unidade_sanitaria->categoriaUnidadeSanitaria->codigo : '',
                    'categoria_unidade_nome' => $unidade_sanitaria->categoriaUnidadeSanitaria ? $unidade_sanitaria->categoriaUnidadeSanitaria->nome : '',
                    'latitude' => $unidade_sanitaria->latitude,
                    'longitude'  => $unidade_sanitaria->longitude,
                ];
            });

        /* $medicamentos = $this->medicamento
            ->with(
                'nomeGenerico',
                'formaMedicamento:id,forma',
                'marcaMedicamentos:id,codigo,marca,pais_origem,medicamento_id'
            )
            ->whereHas('nomeGenerico', function ($query) use ($filtro) {
                $query->where('nome', 'LIKE', '%' . $filtro . '%');
            })
            ->get(['id', 'dosagem', 'nome_generico_medicamento_id', 'forma_medicamento_id'])
            ->map(function ($medicamento) {
                $marcas_medicamento = null;
                if ($medicamento->marcaMedicamentos) {
                    $marcas_medicamento = $medicamento->marcaMedicamentos->map(function ($marca) {
                        return [
                            'marca' => $marca->marca,
                            'codigo' => $marca->codigo
                        ];
                    });
                }

                // dd($marcas_medicamento);
                return [
                    'id' => $medicamento->id,
                    'nome' => empty($medicamento->nomeGenerico) ? '' : $medicamento->nomeGenerico->nome,
                    'forma' => empty($medicamento->formaMedicamento) ? '' : $medicamento->formaMedicamento->forma,
                    'dosagem' => $medicamento->dosagem,
                    'marcas_medicamento' => $marcas_medicamento,
                ];
            }); */

        $data = [
            'farmacias' => $farmacias,
            'unidades_sanitarias' => $unidades_sanitarias,
            // 'medicamentos' => $medicamentos,
        ];

        return $this->sendResponse($data, '', 200);
    }



    public function pesquisarMedicamento($filtro = null)
    {
        // $cliente = null;
        // $stocks_farmacia = null;
        $cliente = Auth::user();
        $cliente->load(['beneficiario', 'dependenteBeneficiario', 'dependenteBeneficiario.beneficiario']);

        $beneficiario = null;
        $grupos_medicamento_plano = [];
        $medicamento_pre_autorizacao_ids = [];
        $medicamento_coberto_ids = [];
        $farmacia_ids = [];

        if (!empty($cliente->beneficiario) && !empty($cliente->dependenteBeneficiario)) {
            return $this->sendError('Ambiguidade na referencia da conta, pertence ao Beneficiáio e Dependente em simultâneo!', 400);
        } else
            if ($cliente->beneficiario) {
            $beneficiario = $cliente->beneficiario;
            $farmacia_ids = $this->farmaciasDoConvenio($beneficiario);
        } else if ($cliente->dependenteBeneficiario) {
            if (empty($cliente->dependenteBeneficiario->beneficiario)) {
                return $this->sendError('O Dependente em causa não possui nenhum Beneficiário associado!', 400);
            }
            $beneficiario = $cliente->dependenteBeneficiario->beneficiario;
            $farmacia_ids = $this->farmaciasDoConvenio($beneficiario);
        }
        // dd($farmacia_ids);
        // if (!empty($cliente->beneficiario) || !empty($cliente->dependenteBeneficiario)) {
        //     dd('Pelo meno um');
        // } else {
        //     dd('Nenhum');
        // }

        if (!empty($beneficiario)) {
            $plano_saude = $this->plano_saude->where('grupo_beneficiario_id', $beneficiario->grupo_beneficiario_id)
                ->with(
                    'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                    'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                    'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id', // if leave id and not medicamento it is ambiguous
                    'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                    'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome'
                )
                ->first();


            // if (empty($plano_saude)) {
            //     $stocks_farmacia = $this->getMedicamentos();
            //     break;
            // }

            if (!empty($plano_saude)) {

                if (!empty($plano_saude->gruposMedicamentoPlano)) {
                    $grupos_medicamento_plano = $plano_saude->gruposMedicamentoPlano->map(function ($grupo_medicamento_plano) {
                        $medicamentos = null;

                        if (!empty($grupo_medicamento_plano->medicamentos)) {
                            $medicamentos = $grupo_medicamento_plano->medicamentos
                                ->map(function ($medicamento) {
                                    return [
                                        'id' => $medicamento->id, // if leave id and not medicamento it is ambiguous
                                        'nome_generico' => empty($medicamento->nomeGenerico) ? '' : $medicamento->nomeGenerico->nome,
                                        'coberto' => $medicamento->pivot->coberto,
                                        'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                                    ];
                                });
                        }
                        return [
                            'medicamentos' => $medicamentos,
                        ];
                    });
                }

                if ($grupos_medicamento_plano) {
                    foreach ($grupos_medicamento_plano->toArray() as $key => $grupo_medicamento_plano) {
                        foreach ($grupo_medicamento_plano['medicamentos'] as $key => $medicamento) {
                            if ($medicamento['coberto'] == true)
                                array_push($medicamento_coberto_ids, $medicamento['id']);

                            if ($medicamento['pre_autorizacao'] == true)
                                array_push($medicamento_pre_autorizacao_ids, $medicamento['id']);
                        }
                    }
                }
                // dd($medicamento_coberto_ids);

                $stocks_farmacia = $this->stock_farmacia
                    ->with([
                        'marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                        'marcaMedicamento.medicamento',
                        'marcaMedicamento.medicamento.nomeGenerico:id,nome',
                        'marcaMedicamento.medicamento.formaMedicamento:id,forma',
                        'medicamento',
                        'medicamento.nomeGenerico',
                        'farmacia'
                    ])
                    ->whereHas('marcaMedicamento', function ($query) use ($filtro) {
                        $query->where('marca', 'LIKE', '%' . $filtro . '%');
                    })
                    ->orWhereHas('medicamento.nomeGenerico', function ($query) use ($filtro) {
                        $query->where('nome', 'LIKE', '%' . $filtro . '%');
                    })
                    ->get()
                    ->map(function ($stock_farmacia) use ($medicamento_coberto_ids, $medicamento_pre_autorizacao_ids, $farmacia_ids) {
                        // return $marca_medicamento;

                        return [
                            'id' => $stock_farmacia->id,
                            'preco' => $stock_farmacia->preco,
                            'iva' => $stock_farmacia->iva,
                            'preco_iva' => $stock_farmacia->preco_iva,
                            'quantidade_disponivel' => $stock_farmacia->quantidade_disponivel,
                            'coberto' => in_array($stock_farmacia->medicamento_id, $medicamento_coberto_ids) ? true : false,
                            'pre_autorizacao' => in_array($stock_farmacia->medicamento_id, $medicamento_pre_autorizacao_ids) ? true : false,
                            'marca_medicamento_id' => $stock_farmacia->marca_medicamento_id,
                            'marca' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->marca,
                            'marca_codigo' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->codigo,
                            'marca_pais_origem' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->pais_origem,
                            // 'medicamento_id' => empty($stock_farmacia->medicamento) ? '' : $stock_farmacia->medicamento->id,
                            'medicamento_codigo' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->codigo,
                            'medicamento_dosagem' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->dosagem,
                            // 'medicamento_nome_generico_id' => empty($stock_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? '' : $stock_farmacia->marcaMedicamento->medicamento->nomeGenerico->id,
                            'medicamento_nome_generico' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->nome,
                            // 'medicamento_forma_id' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->id,
                            'medicamento_forma' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma,
                            'farmacia_id' => !empty($stock_farmacia->farmacia) ? $stock_farmacia->farmacia->id : '',
                            'farmacia_nome' => !empty($stock_farmacia->farmacia) ? $stock_farmacia->farmacia->nome : '',
                            'farmacia_endereco' => !empty($stock_farmacia->farmacia) ? $stock_farmacia->farmacia->endereco : '',
                            'farmacia_convenio' => !empty($stock_farmacia->farmacia) ? (in_array($stock_farmacia->farmacia->id, $farmacia_ids) ? true : false) : false,
                            'horario_funcionamento' => !empty($stock_farmacia->farmacia->horario_funcionamento) ? $stock_farmacia->farmacia->horario_funcionamento : '',

                        ];
                    });
            } else {
                $stocks_farmacia = $this->getMedicamentos();
            }
        } else {
            $stocks_farmacia = $this->getMedicamentos();
        }




        // dd($plano_saude);

        // dd($medicamento_pre_autorizacao_ids);
        $data = [
            'marcas_medicamentos' => $stocks_farmacia,
        ];

        return $this->sendResponse($data, '', 200);
    }

    private function getMedicamentos($filtro = null)
    {
        $stocks_farmacia = null;

        $stocks_farmacia = $this->stock_farmacia
            ->with([
                'marcaMedicamento:id,marca,codigo,pais_origem,medicamento_id',
                'marcaMedicamento.medicamento',
                'marcaMedicamento.medicamento.nomeGenerico:id,nome',
                'marcaMedicamento.medicamento.formaMedicamento:id,forma',
                'medicamento',
                'medicamento.nomeGenerico',
                'farmacia'
            ])
            ->whereHas('marcaMedicamento', function ($query) use ($filtro) {
                $query->where('marca', 'LIKE', '%' . $filtro . '%');
            })
            ->orWhereHas('medicamento.nomeGenerico', function ($query) use ($filtro) {
                $query->where('nome', 'LIKE', '%' . $filtro . '%');
            })
            ->get()
            ->map(function ($stock_farmacia) {
                // return $marca_medicamento;

                return [
                    'id' => $stock_farmacia->id,
                    'preco' => $stock_farmacia->preco,
                    'iva' => $stock_farmacia->iva,
                    'preco_iva' => $stock_farmacia->preco_iva,
                    'quantidade_disponivel' => $stock_farmacia->quantidade_disponivel,
                    'coberto' => false,
                    'pre_autorizacao' => false,
                    'marca_medicamento_id' => $stock_farmacia->marca_medicamento_id,
                    'marca' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->marca,
                    'marca_codigo' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->codigo,
                    'marca_pais_origem' => empty($stock_farmacia->marcaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->pais_origem,
                    // 'medicamento_id' => empty($stock_farmacia->medicamento) ? '' : $stock_farmacia->medicamento->id,
                    'medicamento_codigo' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->codigo,
                    'medicamento_dosagem' => empty($stock_farmacia->marcaMedicamento->medicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->dosagem,
                    // 'medicamento_nome_generico_id' => empty($stock_farmacia->marcaMedicamento->medicamento->nomeGenerico) ? '' : $stock_farmacia->marcaMedicamento->medicamento->nomeGenerico->id,
                    'medicamento_nome_generico' => empty($stock_farmacia->medicamento->nomeGenerico) ? '' : $stock_farmacia->medicamento->nomeGenerico->nome,
                    // 'medicamento_forma_id' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->id,
                    'medicamento_forma' => empty($stock_farmacia->marcaMedicamento->medicamento->formaMedicamento) ? '' : $stock_farmacia->marcaMedicamento->medicamento->formaMedicamento->forma,
                    'farmacia_id' => !empty($stock_farmacia->farmacia) ? $stock_farmacia->farmacia->id : '',
                    'farmacia_nome' => !empty($stock_farmacia->farmacia) ? $stock_farmacia->farmacia->nome : '',
                    'farmacia_endereco' => !empty($stock_farmacia->farmacia) ? $stock_farmacia->farmacia->endereco : '',
                    'horario_funcionamento' => !empty($stock_farmacia->farmacia) ? $stock_farmacia->farmacia->horario_funcionamento : '',
                ];
            });

        return $stocks_farmacia;
    }




    public function getUnidadesSanitariasConvenio()
    {
        $beneficiario = null;
        $unidade_sanitaria_ids = [];
        $farmacia_ids = [];

        $cliente = Auth::user();
        $cliente->load(['beneficiario:id,empresa_id', 'dependenteBeneficiario:id,beneficiario_id', 'dependenteBeneficiario.beneficiario:id,empresa_id']);

        if (!empty($cliente->beneficiario) && !empty($cliente->dependenteBeneficiario)) {
            return $this->sendError('Este cliente encontra-se associado à uma conta de Beneficiário e à uma conta de Dependente. Contacte o Administrador', 400);
        } else if (!empty($cliente->beneficiario)) {
            $beneficiario = $cliente->beneficiario;
        } else if (!empty($dependente_beneficiario = $cliente->dependenteBeneficiario)) {

            if (!empty($dependente_beneficiario->beneficiario)) {
                $beneficiario = $dependente_beneficiario->beneficiario;
            }
        }

        if (!empty($beneficiario)) {

            $unidade_sanitaria_ids = $this->unidadesSanitariasDoConvenio($beneficiario);
            $farmacia_ids = $this->unidadesSanitariasDoConvenio($beneficiario);
        }



        $unidades_sanitarias = $this->unidade_sanitaria->with('categoriaUnidadeSanitaria')
            ->get()
            ->map(function ($unidade_sanitaria) use ($unidade_sanitaria_ids) {
                return [
                    'id' => $unidade_sanitaria->id,
                    'nome' => $unidade_sanitaria->nome,
                    'endereco' => $unidade_sanitaria->endereco,
                    'email' => $unidade_sanitaria->email,
                    'contactos' => $unidade_sanitaria->contactos,
                    'nuit' => $unidade_sanitaria->nuit,
                    'latitude' => $unidade_sanitaria->latitude,
                    'longitude' => $unidade_sanitaria->longitude,
                    'categoria_unidade_sanitaria_id' => $unidade_sanitaria->categoria_unidade_sanitaria_id,
                    'categoria_unidade_sanitaria_nome' => is_null($unidade_sanitaria->categoriaUnidadeSanitaria) ? '' : $unidade_sanitaria->categoriaUnidadeSanitaria->nome,
                    'e_do_conevnio' => in_array($unidade_sanitaria->id, $unidade_sanitaria_ids) ? true : false,
                ];
            });

        $farmacias = $this->farmacia
            ->get()
            ->map(function ($farmacia) use ($farmacia_ids) {
                return [
                    'id' => $farmacia->id,
                    'nome' => $farmacia->nome,
                    'endereco' => $farmacia->endereco,
                    'horario_funcionamento' => $farmacia->horario_funcionamento,
                    'contactos' => $farmacia->contactos,
                    'activa' => $farmacia->activa,
                    'nuit' => $farmacia->nuit,
                    'latitude' => $farmacia->latitude,
                    'longitude' => $farmacia->longitude,
                    'e_do_conevnio' => in_array($farmacia->id, $farmacia_ids) ? true : false,
                ];
            });

        $data = [
            'unidades_sanitarias' => $unidades_sanitarias,
            'farmacias' => $farmacias,
        ];


        return $this->sendResponse($data, 200);
    }



    public function getServicosMedicamentosConvenio()
    {

        $beneficiario = null;
        $plano_saude = null;
        $grupos_medicamento_plano = null;
        $categorias_servico_plano = null;

        $cliente = Auth::user();
        $cliente->load([
            'beneficiario:id,grupo_beneficiario_id',
            'beneficiario.grupoBeneficiario:id',
            'dependenteBeneficiario:id,beneficiario_id',
            'dependenteBeneficiario.beneficiario:id,grupo_beneficiario_id',
            'dependenteBeneficiario.beneficiario.grupoBeneficiario:id',
        ]);

        if (!empty($cliente->beneficiario) && !empty($cliente->dependenteBeneficiario)) {
            return $this->sendError('Este cliente encontra-se associado à uma conta de Beneficiário e à uma conta de Dependente. Contacte o Administrador', 400);
        } else if (!empty($beneficiario = $cliente->beneficiario)) {
            if (!empty($beneficiario->grupoBeneficiario)) {
                $grupo_beneficiario = $beneficiario->grupoBeneficiario;
            }
        } else if (!empty($dependente_beneficiario = $cliente->dependenteBeneficiario)) {
            if (!empty($beneficiario = $dependente_beneficiario->beneficiario)) {
                if (!empty($beneficiario->grupoBeneficiario)) {
                    $grupo_beneficiario = $beneficiario->grupoBeneficiario;
                }
            }
        }
        // dd($grupo_beneficiario);


        if (!empty($grupo_beneficiario)) {
            $plano_saude = $this->plano_saude
                ->where('grupo_beneficiario_id', $grupo_beneficiario->id)
                ->with(
                    'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                    'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                    'gruposMedicamentoPlano.medicamentos:id,sub_grupo_medicamento_id,nome_generico_medicamento_id', // if i leave id, it is ambiguos
                    'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                    'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome',

                    'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                    'categoriasServicoPlano.categoriaServico:id,nome',
                    'categoriasServicoPlano.servicos:id,nome' // if i leave id, it is ambiguos
                )
                ->first();

            // dd($plano_saude);
            if (!empty($plano_saude)) {

                if (!empty($plano_saude->gruposMedicamentoPlano)) {

                    $grupos_medicamento_plano = $plano_saude->gruposMedicamentoPlano->map(function ($grupo_medicamento_plano) {
                        $medicamentos = [];

                        if (!empty($grupo_medicamento_plano->medicamentos)) {
                            $medicamentos = $grupo_medicamento_plano->medicamentos
                                ->filter(function ($medicamento, $key) {
                                    return $medicamento->pivot->coberto == true;
                                })
                                ->values()
                                ->map(function ($medicamento) {
                                    return [
                                        'id' => $medicamento->id, // if i leave id, it is ambiguos
                                        'nome_generico' => empty($medicamento->nomeGenerico) ? '' : $medicamento->nomeGenerico->nome,
                                        'coberto' => $medicamento->pivot->coberto,
                                        'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                                    ];
                                });
                        }


                        return [
                            'id' => $grupo_medicamento_plano->id,
                            'grupo_medicamento_id' => $grupo_medicamento_plano->grupo_medicamento_id,
                            'grupo_medicamento_nome' => empty($grupo_medicamento_plano->grupoMedicamento) ? '' : $grupo_medicamento_plano->grupoMedicamento->nome,
                            'medicamentos' => $medicamentos,
                        ];
                    });
                }


                if (!empty($plano_saude->categoriasServicoPlano)) {
                    $categorias_servico_plano = $plano_saude->categoriasServicoPlano->map(function ($categoria_servico_plano) {
                        $servicos = null;

                        if (!empty($categoria_servico_plano->servicos)) {
                            $servicos = $categoria_servico_plano->servicos
                                ->filter(function ($servico, $key) {
                                    return $servico->pivot->coberto = true;
                                })
                                ->values()
                                ->map(function ($servico) {
                                    return [
                                        'id' => $servico->id,
                                        'nome' => $servico->nome,
                                        'coberto' => $servico->pivot->coberto,
                                        'pre_autorizacao' => $servico->pivot->pre_autorizacao,
                                    ];
                                });
                        }


                        return [
                            'id' => $categoria_servico_plano->id,
                            'categoria_servico_id' => $categoria_servico_plano->categoria_servico_id,
                            'categoria_servico_nome' => is_null($categoria_servico_plano->categoriaServico) ? '' : $categoria_servico_plano->categoriaServico->nome,
                            'servicos' => $servicos,
                        ];
                    });
                }


                $plano_saude = [
                    'id' => $plano_saude->id,
                    'beneficio_anual_segurando_limitado' => $plano_saude->beneficio_anual_segurando_limitado,
                    'valor_limite_anual_segurando' => $plano_saude->valor_limite_anual_segurando,
                    'limite_fora_area_cobertura' => $plano_saude->limite_fora_area_cobertura,
                    'valor_limite_fora_area_cobertura' => $plano_saude->valor_limite_fora_area_cobertura,
                    'regiao_cobertura' => $plano_saude->regiao_cobertura,
                    'grupos_medicamento_plano' => $grupos_medicamento_plano,
                    'categorias_servico_plano' => $categorias_servico_plano,
                ];
            }
        }

        return $this->sendResponse($plano_saude, '', 200);
    }

    // ORIGINAL, já pode ser removido
    /*     public function getServicosMedicamentosConvenio()
    {
        $servicos_convenio = null;
        $plano_saude = null;
        // $categorias_servico_geral = null;

        // $categorias_servico_geral = $this->categoria_servico->with('servicos')->get();

        $cliente = Auth::user();

        if (!is_null($cliente->beneficiario_id)) {

            $beneficiario = Beneficiario::find($cliente->beneficiario_id);
            $grupo_beneficiario = GrupoBeneficiario::find($beneficiario->grupo_beneficiario_id);
            $grupos_medicamento_plano = null;
            $categorias_servico_plano = null;

            if ($grupo_beneficiario) {
                $plano_saude = $this->plano_saude
                    ->where('grupo_beneficiario_id', $grupo_beneficiario->id)
                    ->with(
                        'gruposMedicamentoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,grupo_medicamento_id',
                        'gruposMedicamentoPlano.grupoMedicamento:id,nome',
                        'gruposMedicamentoPlano.medicamentos:medicamento_id,sub_grupo_medicamento_id,nome_generico_medicamento_id',
                        'gruposMedicamentoPlano.medicamentos.nomeGenerico:id,nome',
                        'gruposMedicamentoPlano.medicamentos.subGrupoMedicamento:id,nome',

                        'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                        'categoriasServicoPlano.categoriaServico:id,nome',
                        'categoriasServicoPlano.servicos:servico_id,nome'
                    )
                    ->first();

                // dd($plano_saude);
                if ($plano_saude) {
                    // dd('ss');
                    if (!empty($plano_saude->gruposMedicamentoPlano)) {
                        // dd('nnn');
                        $grupos_medicamento_plano = $plano_saude->gruposMedicamentoPlano->map(function ($grupo_medicamento_plano) {
                            $medicamentos = [];

                            if (!empty($grupo_medicamento_plano->medicamentos)) {
                                $medicamentos = $grupo_medicamento_plano->medicamentos
                                    ->filter(function ($medicamento, $key) {
                                        return $medicamento->pivot->coberto == true;
                                    })
                                    ->values()
                                    ->map(function ($medicamento) {
                                        return [
                                            'id' => $medicamento->medicamento_id,
                                            'nome_generico' => empty($medicamento->nomeGenerico) ? '' : $medicamento->nomeGenerico->nome,
                                            'coberto' => $medicamento->pivot->coberto,
                                            'pre_autorizacao' => $medicamento->pivot->pre_autorizacao,
                                        ];
                                    });
                            }


                            return [
                                'id' => $grupo_medicamento_plano->id,
                                // 'comparticipacao_factura' => $grupo_medicamento_plano->comparticipacao_factura,
                                // 'sujeito_limite_global' => $grupo_medicamento_plano->sujeito_limite_global,
                                // 'beneficio_ilimitado' => $grupo_medicamento_plano->beneficio_ilimitado,
                                // 'valor_beneficio_limitado' => $grupo_medicamento_plano->valor_beneficio_limitado,
                                // 'valor_comparticipacao_factura' => $grupo_medicamento_plano->valor_comparticipacao_factura,
                                'grupo_medicamento_id' => $grupo_medicamento_plano->grupo_medicamento_id,
                                'grupo_medicamento_nome' => empty($grupo_medicamento_plano->grupoMedicamento) ? '' : $grupo_medicamento_plano->grupoMedicamento->nome,
                                'medicamentos' => $medicamentos,
                            ];
                        });
                    }


                    if (!empty($plano_saude->categoriasServicoPlano)) {
                        $categorias_servico_plano = $plano_saude->categoriasServicoPlano->map(function ($categoria_servico_plano) {
                            $servicos = null;

                            if (!empty($categoria_servico_plano->servicos)) {
                                $servicos = $categoria_servico_plano->servicos
                                    ->filter(function ($servico, $key) {
                                        return $servico->pivot->coberto = true;
                                    })
                                    ->values()
                                    ->map(function ($servico) {
                                        return [
                                            'id' => $servico->servico_id,
                                            'nome' => $servico->nome,
                                            'coberto' => $servico->pivot->coberto,
                                            'pre_autorizacao' => $servico->pivot->pre_autorizacao,
                                        ];
                                    });
                            }


                            return [
                                'id' => $categoria_servico_plano->id,
                                // 'comparticipacao_factura' => $categoria_servico_plano->comparticipacao_factura,
                                // 'sujeito_limite_global' => $categoria_servico_plano->sujeito_limite_global,
                                // 'beneficio_ilimitado' => $categoria_servico_plano->beneficio_ilimitado,
                                // 'valor_beneficio_limitado' => $categoria_servico_plano->valor_beneficio_limitado,
                                // 'valor_comparticipacao_factura' => $categoria_servico_plano->valor_comparticipacao_factura,
                                'categoria_servico_id' => $categoria_servico_plano->categoria_servico_id,
                                'categoria_servico_nome' => is_null($categoria_servico_plano->categoriaServico) ? '' : $categoria_servico_plano->categoriaServico->nome,
                                'servicos' => $servicos,
                            ];
                        });
                    }


                    $plano_saude = [
                        'id' => $plano_saude->id,
                        'beneficio_anual_segurando_limitado' => $plano_saude->beneficio_anual_segurando_limitado,
                        'valor_limite_anual_segurando' => $plano_saude->valor_limite_anual_segurando,
                        'limite_fora_area_cobertura' => $plano_saude->limite_fora_area_cobertura,
                        'valor_limite_fora_area_cobertura' => $plano_saude->valor_limite_fora_area_cobertura,
                        'regiao_cobertura' => $plano_saude->regiao_cobertura,
                        // 'grupo_beneficiario_id' => $plano_saude->grupo_beneficiario_id,
                        'grupos_medicamento_plano' => $grupos_medicamento_plano,
                        'categorias_servico_plano' => $categorias_servico_plano,
                    ];
                }
            }
        }

        return $this->sendResponse($plano_saude, '', 200);
    } */

    protected function farmaciasDoConvenio($quem): array
    {
        $farmacia_ids = [];

        if (!empty($quem)) {

            $empresa = $this->empresa->with(['farmacias'])->find($quem->empresa_id);

            if (!empty($empresa)) {

                foreach ($empresa->farmacias as $farmacia) {
                    array_push($farmacia_ids, $farmacia->id);
                }
            }
        }

        return $farmacia_ids;
    }

    protected function unidadesSanitariasDoConvenio($quem): array
    {
        $unidade_sanitaria_ids = [];

        if (!empty($quem)) {

            $empresa = $this->empresa->with(['unidadesSanitarias'])->find($quem->empresa_id);

            if (!empty($empresa)) {

                foreach ($empresa->unidadesSanitarias as $unidade_sanitaria) {
                    array_push($unidade_sanitaria_ids, $unidade_sanitaria->id);
                }
            }
        }

        return $unidade_sanitaria_ids;
    }

    public function historicoConsumo()
    {
        $cliente = Auth::user();
        $cliente->load(['beneficiario:id,empresa_id', 'dependenteBeneficiario:id,beneficiario_id', 'dependenteBeneficiario.beneficiario:id,empresa_id']);
        $beneficiario_ou_dependente_coluna = null;
        $beneficiario_ou_dependente_valor = null;

        if (!empty($cliente->beneficiario)) {
            $beneficiario_ou_dependente_coluna = 'beneficiario_id';
            $beneficiario_ou_dependente_valor = $cliente->beneficiario->id;
        } else if (!empty($cliente->dependenteBeneficiario)) {
            $beneficiario_ou_dependente_coluna = 'dependente_beneficiario_id';
            $beneficiario_ou_dependente_valor = $cliente->dependenteBeneficiario->id;
        } else {
            return $this->sendError('Beneficiario ou Dependente não encontrado!', 404);
        }

        $baixas_farmacia = $this->baixa_farmacia
            // ->byEstado($estado_codigo)
            ->where($beneficiario_ou_dependente_coluna, $beneficiario_ou_dependente_valor)
            // ->orWhere('dependente_beneficiario_id', $dependente_beneficiario_id)
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
                    // 'estado_id' => $baixa_farmacia->estadoBaixa->id,
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

        /** @var BaixaUnidadeSanitaria $baixas_unidade_sanitaria*/
        $baixas_unidade_sanitaria = $this->baixa_unidade_sanitaria
            // ->byEstado($estado_codigo)
            ->where($beneficiario_ou_dependente_coluna, $beneficiario_ou_dependente_valor)
            // ->orWhere('dependente_beneficiario_id', $dependente_beneficiario_id)
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
                        'servico' => !empty($iten_baixa_unidade_sanitaria->servico) ? $iten_baixa_unidade_sanitaria->servico->nome : '',
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
                    // 'estado_id' => $baixa_unidade_sanitaria->estadoBaixa->id,
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

        $pedidos_reembolso = $this->pedido_reembolso
            ->with('beneficiario:id,nome', 'dependenteBeneficiario:id,nome', 'empresa', 'estadoPedidoReembolso:id,nome,codigo')
            ->where($beneficiario_ou_dependente_coluna, $beneficiario_ou_dependente_valor)
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

        $baixas = array_merge($baixas_farmacia, $baixas_unidade_sanitaria);
        $data = [
            'baixas' => $baixas,
            'pedidos_reembolso' => $pedidos_reembolso,
        ];

        return $this->sendResponse($data, 'Baixas Farmacia retrieved successfully!', 200);
    }
}
