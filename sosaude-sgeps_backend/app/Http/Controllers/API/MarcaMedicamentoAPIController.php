<?php

namespace App\Http\Controllers\API;

use Response;
use Exception;
use Illuminate\Http\Request;
use App\Models\MarcaMedicamento;
use Illuminate\Support\Facades\DB;
use App\Models\FormaMarcaMedicamento;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateMarcaMedicamentoAPIRequest;
use App\Http\Requests\API\UpdateMarcaMedicamentoAPIRequest;
use App\Http\Requests\API\CreateUpdateMarcaMedicamentoFormRequest;

/**
 * Class MarcaMedicamentoController
 * @package App\Http\Controllers\API
 */

class MarcaMedicamentoAPIController extends AppBaseController
{
    private $marca_medicamento;
    /**
     * Create a new MarcaMedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(MarcaMedicamento $marca_medicamento)
    {
        $this->marca_medicamento = $marca_medicamento;

        // Check if the current user has one of the roles, those are the codigo atribute and not id of the role
        $this->middleware(["CheckRole:1"]);
    }

    /**
     * Display a listing of the MarcaMedicamento.
     * GET|HEAD /marcaMedicamentos
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $marca_medicamentos = $this->marca_medicamento->get();

        return $this->sendResponse($marca_medicamentos->toArray(), 'Marca Medicamentos retrieved successfully');
    }

    /**
     * Retrive resources for creating a MarcaMediamento.
     * GET|HEAD /marca_medicamentos/create
     *
     * @param Request $request
     * @return Response
     */
    public function create()
    {
        /* $formas_marca_medicamento = $this->forma_marca_medicamento->all();

        return response()->json(['formas_marca_medicamento'=>$formas_marca_medicamento], 200); */
    }

    /**
     * Store a newly created MarcaMedicamento in storage.
     * POST /marcaMedicamentos
     *
     * @param CreateMarcaMedicamentoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateMarcaMedicamentoFormRequest $request)
    {
        $input = $request->all();     

        DB::beginTransaction();
        try {

            /** @var MarcaMedicamento $marca_medicamento */
            $marca_medicamento = MarcaMedicamento::create($input);
            DB::commit();
            return $this->sendResponse($marca_medicamento->toArray(), 'Marca Medicamento saved successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified MarcaMedicamento.
     * GET|HEAD /marcaMedicamentos/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var MarcaMedicamento $marca_medicamento */
        $marca_medicamento = $this->marca_medicamento->find($id);

        if (empty($marca_medicamento)) {
            return $this->sendError('Marca Medicamento not found');
        }

        return $this->sendResponse($marca_medicamento->toArray(), 'Marca Medicamento retrieved successfully');

    }

    /**
     * Update the specified MarcaMedicamento in storage.
     * PUT/PATCH /marcaMedicamentos/{id}
     *
     * @param int $id
     * @param UpdateMarcaMedicamentoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateMarcaMedicamentoFormRequest $request)
    {
        /** @var MarcaMedicamento $marca_medicamento */
        $marca_medicamento = $this->marca_medicamento->find($id);

        if (empty($marca_medicamento)) {
            return $this->sendError('Marca Medicamento not found');
        }

        $marca_medicamento->fill($request->all());

        DB::beginTransaction();
        try {

            /** @var MarcaMedicamento $marca_medicamento */
            $marca_medicamento->save();
            DB::commit();
            return $this->sendResponse($marca_medicamento->toArray(), 'Marca Medicamento updated successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified MarcaMedicamento from storage.
     * DELETE /marcaMedicamentos/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var MarcaMedicamento $marca_medicamento */
        $marca_medicamento = $this->marca_medicamento->find($id);

        if (empty($marca_medicamento)) {
            return $this->sendError('Marca Medicamento not found');
        }

        DB::beginTransaction();
        try {

            /** @var MarcaMedicamento $marca_medicamento */
            $marca_medicamento->delete();
            DB::commit();
            return $this->sendSuccess('Marca Medicamento deleted successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
