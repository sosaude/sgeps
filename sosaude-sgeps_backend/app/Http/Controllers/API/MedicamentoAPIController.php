<?php

namespace App\Http\Controllers\API;

use Response;
use Exception;
use App\Models\Medicamento;
use Illuminate\Http\Request;
use App\Models\FormaMedicamento;
use App\Models\MarcaMedicamento;
use Illuminate\Support\Facades\DB;
use App\Models\NomeGenericoMedicamento;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMedicamentoAPIRequest;
use App\Http\Requests\API\UpdateMedicamentoAPIRequest;
use App\Http\Requests\API\CreateUpdateMedicamentoFormRequest;
use App\Models\GrupoMedicamento;
use App\Models\SubClasseMedicamento;
use App\Models\SubGrupoMedicamento;
use App\Mail\SendMedicamentoMail;

/**
 * Class MedicamentoController
 * @package App\Http\Controllers\API
 */

class MedicamentoAPIController extends AppBaseController
{
    private $nome_generico_medicamento;
    private $medicamento;
    private $forma_medicamento;
    private $grupo_medicamento;
    private $sub_grupo_medicamento;
    private $sub_classe_medicamento;
    private $marca_medicamento;
    /**
     * Create a new MedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(
        Medicamento $medicamento,
        NomeGenericoMedicamento $nome_generico_medicamento,
        FormaMedicamento $forma_medicamento,
        GrupoMedicamento $grupo_medicamento,
        SubGrupoMedicamento $sub_grupo_medicamento,
        SubClasseMedicamento $sub_classe_medicamento,
        MarcaMedicamento $marca_medicamento
    ) {
        $this->medicamento = $medicamento;
        $this->nome_generico_medicamento = $nome_generico_medicamento;
        $this->forma_medicamento = $forma_medicamento;
        $this->grupo_medicamento = $grupo_medicamento;
        $this->sub_grupo_medicamento = $sub_grupo_medicamento;
        $this->sub_classe_medicamento = $sub_classe_medicamento;
        $this->marca_medicamento = $marca_medicamento;

        // Check if the current user has one of the roles, those are the codigo atribute and not id of the role
        $this->middleware(["CheckRole:1"]);
    }

    /**
     * Display a listing of the Medicamento.
     * GET|HEAD /medicamentos
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $medicamentos = $this->medicamento
            ->with('nomeGenerico:id,nome', 'formaMedicamento:id,forma', 'grupoMedicamento:id,nome', 'subGrupoMedicamento:id,nome', 'subClasseMedicamento:id,nome')
            ->withCount('marcaMedicamentos')
            ->get();

        $medicamentos_data = $medicamentos->makeHidden(['nome_generico_medicamento_id', 'forma_medicamento_id', 'grupo_medicamento_id', 'sub_grupo_medicamento_id', 'sub_classe_medicamento_id']);

        return $this->sendResponse($medicamentos_data->toArray(), 'Medicamentos retrieved successfully');
    }

    public function create()
    {
        $nomes_genericos_medicamentos = $this->nome_generico_medicamento->get(['id', 'nome']);
        $formas_medicamento = $this->forma_medicamento->get(['id', 'forma']);
        $grupo_medicamento = $this->grupo_medicamento
            ->with(
                'subGruposMedicamentos:id,nome,grupo_medicamento_id',
                'subGruposMedicamentos.subClassesMedicamentos:id,nome,sub_grupo_medicamento_id'
            )
            ->get(['id', 'nome']);
        // $sub_grupo_medicamento = $this->sub_grupo_medicamento->with('subClassesMedicamentos:id,nome,sub_grupo_medicamento_id')->get(['id', 'nome']);
        // $sub_classe_medicamento = $this->sub_classe_medicamento->get(['id', 'nome']);

        $data = [
            'nomes_genericos_medicamentos' => $nomes_genericos_medicamentos,
            'forma_medicamento' => $formas_medicamento,
            'grupos_medicamentos' => $grupo_medicamento,
            // 'sub_grupos_medicamentos' => $sub_grupo_medicamento,
            // 'sub_classes_medicamentos' => $sub_classe_medicamento
        ];

        return $this->sendResponse($data, 'Formas Medicamento retrieved successfully!', 200);
    }

    /**
     * Store a newly created Medicamento in storage.
     * POST /medicamentos
     *
     * @param CreateMedicamentoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateMedicamentoFormRequest $request)
    {
        $input = $request->validated();

        DB::beginTransaction();
        try {

            /** @var Medicamento $medicamento */
            $medicamento = Medicamento::create($input);
            DB::commit();
            return $this->sendResponse($medicamento->toArray(), 'Medicamento saved successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified Medicamento.
     * GET|HEAD /medicamentos/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Medicamento $medicamento */
        $medicamento = $this->medicamento
            ->with('nomeGenerico:id,nome', 'formaMedicamento:id,forma', 'grupoMedicamento:id,nome', 'subGrupoMedicamento:id,nome', 'subClasseMedicamento:id,nome')
            ->find($id);

        if (empty($medicamento)) {
            return $this->sendError('Medicamento not found');
        }

        $medicamento_data = $medicamento->makeHidden(['nome_generico_medicamento_id', 'forma_medicamento_id', 'grupo_medicamento_id', 'sub_grupo_medicamento_id', 'sub_classe_medicamento_id']);

        return $this->sendResponse($medicamento_data->toArray(), 'Medicamento retrieved successfully');
    }

    /**
     * Update the specified Medicamento in storage.
     * PUT/PATCH /medicamentos/{id}
     *
     * @param int $id
     * @param UpdateMedicamentoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateMedicamentoFormRequest $request)
    {
        /** @var Medicamento $medicamento */
        $medicamento = Medicamento::find($id);

        if (empty($medicamento)) {
            return $this->sendError('Medicamento not found');
        }

        $input = $request->validated();

        DB::beginTransaction();
        try {

            $medicamento->update($input);
            DB::commit();
            return $this->sendResponse($medicamento->toArray(), 'Medicamento updated successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified Medicamento from storage.
     * DELETE /medicamentos/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Medicamento $medicamento */
        $medicamento = Medicamento::find($id);

        if (empty($medicamento)) {
            return $this->sendError('Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $medicamento->delete();
            DB::commit();
            return $this->sendSuccess('Medicamento deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Retrive the MarcaMedicamento of specified Medicamento from storage.
     * GET /medicamentos/marcas/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function mracasMedicamento($id)
    {
        /** @var MarcaMedicamento $marca_medicamento */
        $marcas_medicamento = $this->marca_medicamento->where('medicamento_id', $id)->get();
        return $this->sendResponse($marcas_medicamento->toArray(), 'Marcas Medicamento retrieved successfully');
    }
}
