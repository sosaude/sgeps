<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SubGrupoMedicamento;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AppBaseController;

class SubGrupoMedicamentoAPIController extends AppBaseController
{
    private $sub_grupo_medicamento;
    /**
     * Create a new SubGrupoMedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(SubGrupoMedicamento $sub_grupo_medicamento)
    {
        $this->middleware(["CheckRole:1"]);
        $this->sub_grupo_medicamento = $sub_grupo_medicamento;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sub_grupos_medicamentos = $this->sub_grupo_medicamento
            ->with('grupoMedicamentos:id,nome')
            ->orderBy('nome', 'ASC')
            ->get(['id', 'nome', 'grupo_medicamento_id']);

        $sub_grupos_medicamentos->makeHidden(['grupo_medicamento_id']);

        return $this->sendResponse($sub_grupos_medicamentos, 'Sub-Grupos Medicamentos retrieved successfully', 200);
    }

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
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:sub_grupo_medicamentos,nome',
            'grupo_medicamento_id' => 'required|integer|exists:grupo_medicamentos,id'
        ]);
        $input = $request->only(['nome', 'grupo_medicamento_id']);

        DB::beginTransaction();
        try {

            /** @var subGrupoMedicamento $sub_grupo_medicamento */
            $sub_grupo_medicamento = $this->sub_grupo_medicamento->create($input);
            DB::commit();
            return $this->sendResponse($sub_grupo_medicamento->toArray(), 'Sub-Grupo Medicamento saved successfully');
        } catch (Exception $e) {
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
        /** @var subGrupoMedicamento $sub_grupo_medicamento */
        $sub_grupo_medicamento = $this->sub_grupo_medicamento
        ->with('grupoMedicamentos:id,nome')
        ->find($id);

        if (empty($sub_grupo_medicamento)) {
            return $this->sendError('Sub-Grupo Medicamento not found');
        }
        $sub_grupo_medicamento->makeHidden(['grupo_medicamento_id']);
        return $this->sendResponse($sub_grupo_medicamento->toArray(), 'Sub-Grupo Medicamento retrieved successfully');
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
        /** @var subGrupoMedicamento $sub_grupo_medicamento */
        $sub_grupo_medicamento = $this->sub_grupo_medicamento->find($id);

        $request->validate([
            'nome' => 'required|string|max:255|unique:sub_grupo_medicamentos,nome,' . $id . ',id',
            'grupo_medicamento_id' => 'required|integer|exists:grupo_medicamentos,id'
        ]);

        $input = $request->only(['nome', 'grupo_medicamento_id']);

        if (empty($sub_grupo_medicamento)) {
            return $this->sendError('Sub-Grupo Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $sub_grupo_medicamento->update($input);
            DB::commit();
            return $this->sendResponse($sub_grupo_medicamento->toArray(), 'Sub-Grupo Medicamento updated successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /** @var SubGrupoMedicamento $sub_grupo_medicamento */
        $sub_grupo_medicamento = $this->sub_grupo_medicamento->find($id);

        if (empty($sub_grupo_medicamento)) {
            return $this->sendError('Sub-Grupo Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $sub_grupo_medicamento->delete();
            DB::commit();
            return $this->sendSuccess('Sub-Grupo Medicamento deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
