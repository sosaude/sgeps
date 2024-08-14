<?php

namespace App\Http\Controllers\API\UnidadeSanitaria;

use App\Models\User;
use App\Models\Servico;
use App\Models\PlanoSaude;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\ServicoUnidadeSanitaria;
use App\Http\Controllers\AppBaseController;

class ServicoUnidadeSanitariaAPIController extends AppBaseController
{
    private $servico;
    private $servico_unidade_sanitaria;
    private $plano_saude;

    public function __construct(ServicoUnidadeSanitaria $servico_unidade_sanitaria, Servico $servico, PlanoSaude $plano_saude)
    {
        $this->servico = $servico;
        $this->servico_unidade_sanitaria = $servico_unidade_sanitaria;
        $this->plano_saude = $plano_saude;
    }

    public function getServicosAdministracao()
    {
        if (Gate::denies('gerir serviço')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // dd(request());
        $servicos = [];

        $servicos = $this->servico->get(['id', 'nome'])->map(function ($servico) {
            return [
                'servico_id' => $servico->id,
                'servico_nome' => $servico->nome,
            ];
        });

        $data = [
            'servicos' => $servicos,
        ];

        return $this->sendResponse($data, '', 200);
    }


    public function getServicosUnidadeSanitaria()
    {
        if (Gate::denies('gerir serviço')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request('unidade_sanitaria_id');

        $servicos_unidade_sanitaria = $this->servico_unidade_sanitaria
            ->byUnidadeSanitaria($unidade_sanitaria_id)
            ->with([
                'servico:id,nome',
            ])
            ->get()
            ->map(function ($servico_unidade_sanitaria) {

                return [
                    'id' => $servico_unidade_sanitaria->id,
                    'preco' => $servico_unidade_sanitaria->preco,
                    'iva' => $servico_unidade_sanitaria->iva,
                    'preco_iva' => $servico_unidade_sanitaria->preco_iva,
                    'servico_id' => $servico_unidade_sanitaria->servico_id,
                    'servico_nome' => !empty($servico_unidade_sanitaria->servico) ? $servico_unidade_sanitaria->servico->nome : '',
                ];
            });


        $data = [
            'servicos_unidade_sanitaria' => $servicos_unidade_sanitaria,
        ];

        return $this->sendResponse($data, '', 200);
    }



    public function getStockIniciarVenda($beneficiario_id)
    {
        if (Gate::denies('gerir serviço')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $request->validate(['beneficiario_id' => 'required|integer']);
        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        // $beneficiario_id = $request->beneficiario_id;
        $categorias_servico_plano = [];
        $servico_coberto_ids = [];
        $servico_pre_autorizacao_ids = [];

        $beneficiao = Beneficiario::where('id', $beneficiario_id)->first();
        if (empty($beneficiao))
            return $this->sendError('Beneficiário não encontrado!', 404);

        $plano_saude = $this->plano_saude->where('grupo_beneficiario_id', $beneficiao->grupo_beneficiario_id)
            ->with(
                'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                'categoriasServicoPlano.categoriaServico:id,nome',
                'categoriasServicoPlano.servicos:id,nome' // if leave id instead of servico_id it is ambiguous
            )
            ->first();

        if (empty($plano_saude))
            return $this->sendError('Plano de Saúde do Beneficiário não encontrado!', 404);

        if (!empty($plano_saude->categoriasServicoPlano)) {
            $categorias_servico_plano = $plano_saude->categoriasServicoPlano->map(function ($categoria_servico_plano) {
                $servicos = null;

                if (!empty($categoria_servico_plano->servicos)) {
                    $servicos = $categoria_servico_plano->servicos
                        ->map(function ($servico) {
                            return [
                                'id' => $servico->id, // if leave id instead of servico_id it is ambiguous
                                'servico' => $servico->nome,
                                'coberto' => $servico->pivot->coberto,
                                'pre_autorizacao' => $servico->pivot->pre_autorizacao,
                            ];
                        });
                }
                return [
                    'servicos' => $servicos,
                ];
            });
        }

        // Pegar os ids dos servicos que precisam da pre-autorizacao a partir das categorias_servico_plano do plano encontrado acima
        if ($categorias_servico_plano) {
            foreach ($categorias_servico_plano->toArray() as $key => $categoria_servico_plano) {
                foreach ($categoria_servico_plano['servicos'] as $key => $servico) {
                    if ($servico['coberto'] == true)
                        array_push($servico_coberto_ids, $servico['id']);

                    if ($servico['pre_autorizacao'] == true)
                        array_push($servico_pre_autorizacao_ids, $servico['id']);
                }
            }
        }


        $servicos_unidade_sanitaria = $this->servico_unidade_sanitaria
            ->byUnidadeSanitaria($unidade_sanitaria_id)
            ->with([
                'servico:id,nome',
            ])
            ->get()
            ->map(function ($servico_unidade_sanitaria) use($servico_coberto_ids, $servico_pre_autorizacao_ids) {

                return [
                    'id' => $servico_unidade_sanitaria->id,
                    'preco' => $servico_unidade_sanitaria->preco,
                    'iva' => $servico_unidade_sanitaria->iva,
                    'preco_iva' => $servico_unidade_sanitaria->preco_iva,
                    'coberto' => in_array($servico_unidade_sanitaria->servico_id, $servico_coberto_ids) ? true : false,
                    'pre_autorizacao' => in_array($servico_unidade_sanitaria->servico_id, $servico_pre_autorizacao_ids) ? true : false,
                    'servico_id' => $servico_unidade_sanitaria->servico_id,
                    'servico_nome' => !empty($servico_unidade_sanitaria->servico) ? $servico_unidade_sanitaria->servico->nome : '',
                ];
            });
        // dd($servico_pre_autorizacao_ids);
        $data = [
            'servicos_unidade_sanitaria' => $servicos_unidade_sanitaria,
        ];

        return $this->sendResponse($data, '', 200);
    }


    public function getStockIniciarPedidoAprovacao($beneficiario_id)
    {
        if (Gate::denies('gerir serviço')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $request->validate(['beneficiario_id' => 'required|integer']);
        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        // $beneficiario_id = $request->beneficiario_id;
        $categorias_servico_plano = [];
        $servico_pre_autorizacao_ids = [];
        $servico_coberto_ids = [];

        $beneficiao = Beneficiario::where('id', $beneficiario_id)->first();
        if (empty($beneficiao))
            return $this->sendError('Beneficiário não encontrado!', 404);

        $plano_saude = $this->plano_saude->where('grupo_beneficiario_id', $beneficiao->grupo_beneficiario_id)
            ->with(
                'categoriasServicoPlano:id,comparticipacao_factura,sujeito_limite_global,beneficio_ilimitado,valor_beneficio_limitado,valor_comparticipacao_factura,plano_saude_id,categoria_servico_id',
                'categoriasServicoPlano.categoriaServico:id,nome',
                'categoriasServicoPlano.servicos:id,nome' // if leave id instead of servico_id it is ambiguous
            )
            ->first();

        if (empty($plano_saude))
            return $this->sendError('Plano de Saúde do Beneficiário não encontrado!', 404);

        if (!empty($plano_saude->categoriasServicoPlano)) {
            $categorias_servico_plano = $plano_saude->categoriasServicoPlano->map(function ($categoria_servico_plano) {
                $servicos = null;

                if (!empty($categoria_servico_plano->servicos)) {
                    $servicos = $categoria_servico_plano->servicos
                        ->filter(function ($servico, $key) {
                            return $servico->pivot->pre_autorizacao == true;
                        })
                        ->values()
                        ->map(function ($servico) {
                            return [
                                'id' => $servico->id, // if leave id instead of servico_id it is ambiguous
                                'servico' => $servico->nome,
                                'coberto' => $servico->pivot->coberto,
                                'pre_autorizacao' => $servico->pivot->pre_autorizacao,
                            ];
                        });
                }
                return [
                    'servicos' => $servicos,
                ];
            });
        }

        // Pegar os ids dos servicos que precisam da pre-autorizacao a partir das categorias_servico_plano do plano encontrado acima
        if ($categorias_servico_plano) {
            foreach ($categorias_servico_plano->toArray() as $key => $categoria_servico_plano) {
                foreach ($categoria_servico_plano['servicos'] as $key => $servico) {
                    // array_push($servico_pre_autorizacao_ids, $servico['id']);
                    if ($servico['coberto'] == true)
                        array_push($servico_coberto_ids, $servico['id']);

                    if ($servico['pre_autorizacao'] == true)
                        array_push($servico_pre_autorizacao_ids, $servico['id']);
                }
            }
        }

// dd($plano_saude);
        $servicos_unidade_sanitaria = $this->servico_unidade_sanitaria
            ->byUnidadeSanitaria($unidade_sanitaria_id)
            ->whereIn('servico_id', $servico_pre_autorizacao_ids)
            ->with([
                'servico:id,nome',
            ])
            ->get()
            ->map(function ($servico_unidade_sanitaria) use ($servico_coberto_ids, $servico_pre_autorizacao_ids) {

                return [
                    'id' => $servico_unidade_sanitaria->id,
                    'preco' => $servico_unidade_sanitaria->preco,
                    'iva' => $servico_unidade_sanitaria->iva,
                    'preco_iva' => $servico_unidade_sanitaria->preco_iva,
                    'coberto' => in_array($servico_unidade_sanitaria->servico_id, $servico_coberto_ids) ? true : false,
                    'pre_autorizacao' => in_array($servico_unidade_sanitaria->servico_id, $servico_pre_autorizacao_ids) ? true : false,
                    'servico_id' => $servico_unidade_sanitaria->servico_id,
                    'servico_nome' => !empty($servico_unidade_sanitaria->servico) ? $servico_unidade_sanitaria->servico->nome : '',
                ];
            });
        // dd($servico_pre_autorizacao_ids);
        $data = [
            'servicos_unidade_sanitaria' => $servicos_unidade_sanitaria,
        ];

        return $this->sendResponse($data, '', 200);
    }


    public function setServicosUnidadeSanitaria(Request $request)
    {
        if (Gate::denies('gerir serviço')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = $request->unidade_sanitaria_id;
        $request->validate([
            'servico_id' => 'required|integer',
            'preco' => 'required|numeric',
            'iva' => 'required|numeric',
            'preco_iva' => 'required|numeric',
            'unidade_sanitaria_id' => 'required|integer',
            'servico_id' => "unique:servico_unidade_sanitarias,servico_id,NULL,id,unidade_sanitaria_id,$unidade_sanitaria_id",
        ]);

        $input = $request->only(['servico_id', 'preco', 'iva', 'preco_iva', 'unidade_sanitaria_id']);

        DB::beginTransaction();
        try {
            $servico_unidade_sanitaria = $this->servico_unidade_sanitaria->create($input);

            $data = [
                'id' => $servico_unidade_sanitaria->id,
                'preco' => $servico_unidade_sanitaria->preco,
                'iva' => $servico_unidade_sanitaria->iva,
                'preco_iva' => $servico_unidade_sanitaria->preco_iva,
                'servico_id' => $servico_unidade_sanitaria->servico_id,
                'servico_nome' => !empty($servico_unidade_sanitaria->servico) ? $servico_unidade_sanitaria->servico->nome : '',
            ];

            DB::commit();
            return $this->sendResponse($data, '', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getTrace());
        }
    }

    public function verificarBeneficiario(Request $request)
    {
        if (Gate::denies('gerir verificação beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $request->validate(['codigo' => 'required|string']);
        $codigo = $request->codigo;
        $beneficiario = User::with('beneficiario')->where('codigo_login', $codigo)->first();

        if (empty($beneficiario))
            return $this->sendError('Beneficiário não encontrado!', 404);

        $data = [
            'o_que_devolver' => '?',
        ];

        return $this->sendResponse($data, '', 200);
    }


    public function actualizarStock(Request $request)
    {
        
        if (Gate::denies('gerir serviço')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }


        $request->validate([
            'id' => 'required|integer',
            'servico_id' => 'required|integer',
            'preco' => 'required|numeric',
            'iva' => 'required|numeric',
            'preco_iva' => 'required|numeric',
            'unidade_sanitaria_id' => 'required|integer',
            'servico_id' => "unique:servico_unidade_sanitarias,servico_id,$request->id,id,unidade_sanitaria_id,$request->unidade_sanitaria_id",
        ]);

        $input = $request->only(['servico_id', 'preco', 'iva', 'preco_iva', 'unidade_sanitaria_id']);
        // $unidade_sanitaria_id = $request->unidade_sanitaria_id;

        $servico_unidade_sanitaria = $this->servico_unidade_sanitaria
            ->where('id', $request->id)
            ->where('unidade_sanitaria_id', $request->unidade_sanitaria_id)
            ->first();
        if (empty($servico_unidade_sanitaria))
            return $this->sendError('Item não encontrado!', 404);

        DB::beginTransaction();
        try {
            $servico_unidade_sanitaria->update($input);

            $data = [
                'id' => $servico_unidade_sanitaria->id,
                'preco' => $servico_unidade_sanitaria->preco,
                'iva' => $servico_unidade_sanitaria->iva,
                'preco_iva' => $servico_unidade_sanitaria->preco_iva,
                'servico_id' => $servico_unidade_sanitaria->servico_id,
                'servico_nome' => !empty($servico_unidade_sanitaria->servico) ? $servico_unidade_sanitaria->servico->nome : '',
            ];

            DB::commit();
            return $this->sendResponse($data, '', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getTrace());
        }
    }

    public function removerServicoUnidadeSanitaria($id)
    {
        $unidade_sanitaria_id = request('unidade_sanitaria_id');

        $servico_unidade_sanitaria = $this->servico_unidade_sanitaria
            ->where('id', $id)
            ->where('unidade_sanitaria_id', $unidade_sanitaria_id)
            ->first();

        if (empty($servico_unidade_sanitaria))
            return $this->sendError('Item não encontrado!', 404);

        DB::beginTransaction();
        try {
            $servico_unidade_sanitaria->delete();
            DB::commit();

            return $this->sendSuccess('Removido com sucesso!', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getTrace());
        }
    }
}
