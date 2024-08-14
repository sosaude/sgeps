<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\GrupoMedicamento;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AppBaseController;

class GrupoMedicamentoAPIController extends AppBaseController
{
    private $grupo_medicamento;
    /**
     * Create a new GrupoMedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(GrupoMedicamento $grupo_medicamento)
    {
        $this->middleware(["CheckRole:1"]);
        $this->grupo_medicamento = $grupo_medicamento;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $grupos_medicamentos = $this->grupo_medicamento->orderBy('nome', 'ASC')->get(['id', 'nome']);

        return $this->sendResponse($grupos_medicamentos, 'Grupos Medicamentos retrieved successfully', 200);
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
            'nome' => 'required|string|max:255|unique:grupo_medicamentos,nome'
        ]);
        $input = $request->only(['nome']);

        DB::beginTransaction();
        try {

            /** @var GrupoMedicamento $grupo_medicamento */
            $grupo_medicamento = $this->grupo_medicamento->create($input);
            DB::commit();
            return $this->sendResponse($grupo_medicamento->toArray(), 'Grupo Medicamento saved successfully');
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
        /** @var GrupoMedicamento $grupo_medicamento */
        $grupo_medicamento = $this->grupo_medicamento->find($id);

        if (empty($grupo_medicamento)) {
            return $this->sendError('Grupo Medicamento not found');
        }

        return $this->sendResponse($grupo_medicamento->toArray(), 'Grupo Medicamento retrieved successfully');
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
        /** @var GrupoMedicamento $grupo_medicamento */
        $grupo_medicamento = $this->grupo_medicamento->find($id);

        $request->validate([
            'nome' => 'required|string|max:255|unique:grupo_medicamentos,nome,'.$id.',id'
        ]);
        
        $input = $request->only(['nome']);

        if (empty($grupo_medicamento)) {
            return $this->sendError('Grupo Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $grupo_medicamento->update($input);
            DB::commit();
            return $this->sendResponse($grupo_medicamento->toArray(), 'Grupo Medicamento updated successfully');

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
        /** @var GrupoMedicamento $grupo_medicamento */
        $grupo_medicamento = $this->grupo_medicamento->find($id);

        if (empty($grupo_medicamento)) {
            return $this->sendError('Grupo Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $grupo_medicamento->delete();
            DB::commit();
            return $this->sendSuccess('Grupo Medicamento deleted successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
