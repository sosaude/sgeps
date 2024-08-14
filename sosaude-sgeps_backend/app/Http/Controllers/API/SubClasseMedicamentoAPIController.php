<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\SubClasseMedicamento;
use App\Http\Controllers\AppBaseController;

class SubClasseMedicamentoAPIController extends AppBaseController
{
    private $sub_classe_medicamento;
    /**
     * Create a new SubGrupoMedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(SubClasseMedicamento $sub_classe_medicamento)
    {
        $this->middleware(["CheckRole:1"]);
        $this->sub_classe_medicamento = $sub_classe_medicamento;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sub_classes_medicamentos = $this->sub_classe_medicamento
        ->with('subGrupoMedicamentos:id,nome')
        ->orderBy('nome', 'ASC')
        ->get(['id', 'nome', 'sub_grupo_medicamento_id']);
        $sub_classes_medicamentos->makeHidden(['sub_grupo_medicamento_id']);
        return $this->sendResponse($sub_classes_medicamentos, 'Sub-Grupos Medicamentos retrieved successfully', 200);
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
            'nome' => 'required|string|max:255|unique:sub_classe_medicamentos,nome',
            'sub_grupo_medicamento_id' => 'required|integer|exists:sub_grupo_medicamentos,id'
        ]);
        $input = $request->only(['nome', 'sub_grupo_medicamento_id']);

        DB::beginTransaction();
        try {

            /** @var SubClasseMedicamento $sub_classe_medicamento */
            $sub_classe_medicamento = $this->sub_classe_medicamento->create($input);
            DB::commit();
            return $this->sendResponse($sub_classe_medicamento->toArray(), 'Sub-Classe Medicamento saved successfully');
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
        /** @var SubClasseMedicamento $sub_classe_medicamento */
        $sub_classe_medicamento = $this->sub_classe_medicamento
        ->with('subGrupoMedicamentos:id,nome')
        ->find($id);

        if (empty($sub_classe_medicamento)) {
            return $this->sendError('Sub-Classe Medicamento not found');
        }
        $sub_classe_medicamento->makeHidden(['sub_grupo_medicamento_id']);
        return $this->sendResponse($sub_classe_medicamento->toArray(), 'Sub-Classe Medicamento retrieved successfully');
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
        /** @var subClasseMedicamento $sub_classe_medicamento */
        $sub_classe_medicamento = $this->sub_classe_medicamento->find($id);

        $request->validate([
            'nome' => 'required|string|max:255|unique:sub_classe_medicamentos,nome,'.$id.',id',
            'sub_grupo_medicamento_id' => 'required|integer|exists:sub_grupo_medicamentos,id'
        ]);
        
        $input = $request->only(['nome', 'sub_grupo_medicamento_id']);

        if (empty($sub_classe_medicamento)) {
            return $this->sendError('Sub-Classe Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $sub_classe_medicamento->update($input);
            DB::commit();
            return $this->sendResponse($sub_classe_medicamento->toArray(), 'Sub-Classe Medicamento updated successfully');

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
        /** @var SubClasseMedicamento $sub_classe_medicamento */
        $sub_classe_medicamento = $this->sub_classe_medicamento->find($id);

        if (empty($sub_classe_medicamento)) {
            return $this->sendError('Sub-Classe Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $sub_classe_medicamento->delete();
            DB::commit();
            return $this->sendSuccess('Sub-Classe Medicamento deleted successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
