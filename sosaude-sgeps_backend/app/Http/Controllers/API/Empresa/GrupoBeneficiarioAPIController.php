<?php

namespace App\Http\Controllers\API\Empresa;

use Response;
use Illuminate\Http\Request;
use App\Models\GrupoBeneficiario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\QueryException;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Tenant\Empresa\CreateUpdateGrupoBeneficiarioFormRequest;

/**
 * Class GrupoBeneficiarioController
 * @package App\Http\Controllers\API
 */

class GrupoBeneficiarioAPIController extends AppBaseController
{
    private $grupo_beneficiario;

    public function __construct(GrupoBeneficiario $grupo_beneficiario)
    {
        $this->grupo_beneficiario = $grupo_beneficiario;
    }
    /**
     * Display a listing of the GrupoBeneficiario.
     * GET|HEAD /grupo_beneficiarios
     *
     * @return Response
     */
    public function index()
    {
        if (Gate::denies('gerir grupo beneficiário')) {
            return $this->sendError('This action is unauthorized.', 403);
        }

        $empresa_id = request('empresa_id');

        $grupos_beneficiario = $this->grupo_beneficiario
            ->byEmpresa($empresa_id)
            ->get()
            ->map(function ($grupo_bene) {
                $grupo_bene->numero_beneficiarios = $grupo_bene->beneficiarios->count();
                return $grupo_bene->only('id', 'nome', 'numero_beneficiarios');
            });

        return $this->sendResponse($grupos_beneficiario->toArray(), 'Grupo Beneficiarios retrieved successfully', 200);
    }

    /**
     * Store a newly created GrupoBeneficiario in storage.
     * POST /GrupoBeneficiarios
     *
     * @param CreateUpdateGrupoBeneficiarioFormRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateGrupoBeneficiarioFormRequest $request)
    {
        if (Gate::denies('gerir grupo beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $input = $request->all();

        DB::beginTransaction();
        try {

            /** @var GrupoBeneficiario $grupo_beneficiario */
            $grupo_beneficiario = $this->grupo_beneficiario->create($input);

            DB::commit();
            $data = [
                'id' => $grupo_beneficiario->id,
                'nome' => $grupo_beneficiario->nome,
            ];
            return $this->sendResponse($data, 'Grupo Beneficiario registado com sucesso.', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage, 404);
        }
    }

    /**
     * Display the specified GrupoBeneficiario.
     * GET|HEAD /GrupoBeneficiarios/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        if (Gate::denies('gerir grupo beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');

        /** @var GrupoBeneficiario $GrupoBeneficiario */
        $grupo_beneficiario = $this->grupo_beneficiario->byEmpresa($empresa_id)->find($id);

        if (empty($grupo_beneficiario)) {
            return $this->sendError('Grupo Beneficiario não encontrado.', 404);
        }

        return $this->sendResponse($grupo_beneficiario->toArray(), 'Grupo Beneficiario retornado com sucesso.', 200);
    }

    /**
     * Update the specified GrupoBeneficiario in storage.
     * PUT/PATCH /grupo_beneficiario/{id}
     *
     * @param int $id
     * @param CreateUpdateGrupoBeneficiarioFormRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateGrupoBeneficiarioFormRequest $request)
    {
        if (Gate::denies('gerir grupo beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');

        /** @var GrupoBeneficiario $grupo_beneficiario */
        $grupo_beneficiario = $this->grupo_beneficiario->byEmpresa($empresa_id)->find($id);

        if (empty($grupo_beneficiario)) {
            return $this->sendError('Grupo Beneficiario não encontrado.', 404);
        }

        DB::beginTransaction();
        try {

            $grupo_beneficiario->update($request->all());
            $data = [
                'id' => $grupo_beneficiario->id,
                'nome' => $grupo_beneficiario->nome,
            ];

            DB::commit();
            return $this->sendResponse($data, 'Grupo Beneficiario actualizado com sucesso.', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage, 404);
        }
    }

    /**
     * Remove the specified GrupoBeneficiario from storage.
     * DELETE /grupo_beneficiario/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (Gate::denies('gerir grupo beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');

        /** @var GrupoBeneficiario $grupo_beneficiario */
        $grupo_beneficiario = $this->grupo_beneficiario
            ->byEmpresa($empresa_id)
            ->with([
                'beneficiarios',
                'planoSaude',
                'planoSaude.gruposMedicamentoPlano',
                'planoSaude.gruposMedicamentoPlano.medicamentos',
                'planoSaude.categoriasServicoPlano',
                'planoSaude.categoriasServicoPlano.servicos'
            ])
            ->find($id);

        if (empty($grupo_beneficiario)) {
            return $this->sendError('Grupo Beneficiario not found', 404);
        }

        if ($grupo_beneficiario->beneficiarios->isNotEmpty()) {
            return $this->sendError('Grupo Beneficiário não pode ser removido pois possuí Beneficiários associados!', 404);
        }

        DB::beginTransaction();
        try {

            if (!empty($plano_saude = $grupo_beneficiario->planoSaude)) {

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
            }

            $grupo_beneficiario->delete();

            DB::commit();
            return $this->sendSuccess('Grupo Beneficiario deleted successfully', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage, 404);
        } catch (QueryException $e) {
            DB::rollback();
            return $this->sendError("Ocorreu um erro de integridade referêncial na Base de Dados, não pode remover este item. Contacte o Administrador!" . $e->getMessage(), 404);
        }
    }
}
