<?php

namespace App\Http\Controllers\API;

use Response;
use App\Models\Servico;
use Illuminate\Http\Request;
use App\Models\CategoriaServico;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateServicoAPIRequest;
use App\Http\Requests\API\UpdateServicoAPIRequest;
use App\Http\Requests\API\CreateUpdateServicoFormRequest;

/**
 * Class ServicoController
 * @package App\Http\Controllers\API
 */

class ServicoAPIController extends AppBaseController
{
    private $servico;
    private $categoria_servico;
    /**
     * Create a new MarcaMedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(Servico $servico, CategoriaServico $categoria_servico)
    {
        $this->servico = $servico;
        $this->categoria_servico = $categoria_servico;

        // Check if the current user has one of the roles, those are the codigo atribute and not id of the role
        $this->middleware(["CheckRole:1"]);
    }

    /**
     * Display a listing of the Servico.
     * GET|HEAD /servicos
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $servicos = $this->servico->with('categoriaServico:id,nome')->get();
        $servicos->makeHidden(['categoria_servico_id']);

        return $this->sendResponse($servicos->toArray(), 'Servicos retrieved successfully');
    }

    public function create()
    {
        $categorias_servicos = $this->categoria_servico->orderBy('nome', 'ASC')->get(['id', 'nome']);

        $data = [
            'categorias_serivicos' => $categorias_servicos
        ];

        return $this->sendResponse($data,'',200);
    }

    /**
     * Store a newly created Servico in storage.
     * POST /servicos
     *
     * @param CreateServicoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateServicoFormRequest $request)
    {
        $input = $request->all();

        DB::beginTransaction();
        try {

            /** @var Servico $servico */
            $servico = Servico::create($input);
            DB::commit();
            return $this->sendResponse($servico->toArray(), 'Servico saved successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified Servico.
     * GET|HEAD /servicos/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Servico $servico */
        $servico = Servico::with('categoriaServico:id,nome')->find($id);
        $servico->makeHidden(['categoria_servico_id']);

        if (empty($servico)) {
            return $this->sendError('Servico not found');
        }

        return $this->sendResponse($servico->toArray(), 'Servico retrieved successfully');
    }

    /**
     * Update the specified Servico in storage.
     * PUT/PATCH /servicos/{id}
     *
     * @param int $id
     * @param UpdateServicoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateServicoFormRequest $request)
    {
        /** @var Servico $servico */
        $servico = Servico::find($id);

        if (empty($servico)) {
            return $this->sendError('Servico not found');
        }

        $servico->fill($request->all());

        DB::beginTransaction();
        try {

            $servico->save();
            DB::commit();
            return $this->sendResponse($servico->toArray(), 'Servico updated successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified Servico from storage.
     * DELETE /servicos/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Servico $servico */
        $servico = Servico::find($id);

        if (empty($servico)) {
            return $this->sendError('Servico not found');
        }

        DB::beginTransaction();
        try {

            $servico->delete();
            DB::commit();
            return $this->sendSuccess('Servico deleted successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
