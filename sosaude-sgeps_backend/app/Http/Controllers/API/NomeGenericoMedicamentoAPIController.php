<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\NomeGenericoMedicamento;
use App\Http\Controllers\AppBaseController;

class NomeGenericoMedicamentoAPIController extends AppBaseController
{
    private $nome_generico_medicamento;
    /**
     * Create a new GrupoMedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(NomeGenericoMedicamento $nome_generico_medicamento)
    {
        $this->middleware(["CheckRole:1"]);
        $this->nome_generico_medicamento = $nome_generico_medicamento;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nomes_genericos_medicamentos = $this->nome_generico_medicamento->orderBy('nome', 'ASC')->get(['id', 'nome']);

        return $this->sendResponse($nomes_genericos_medicamentos, 'Nomes Genéricos Medicamentos retrieved successfully', 200);
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
            'nome' => 'required|string|max:255|unique:nome_generico_medicamentos,nome'
        ]);
        $input = $request->only(['nome']);

        DB::beginTransaction();
        try {

            $nome_generico_medicamento = $this->nome_generico_medicamento->create($input);
            DB::commit();
            return $this->sendResponse($nome_generico_medicamento->toArray(), 'Nome Genérico Medicamento saved successfully');
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
        $nome_generico_medicamento = $this->nome_generico_medicamento->find($id);

        if (empty($nome_generico_medicamento)) {
            return $this->sendError('Nome Genérico Medicamento not found');
        }

        return $this->sendResponse($nome_generico_medicamento->toArray(), 'Nome Genérico Medicamento retrieved successfully');
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
        $nome_generico_medicamento = $this->nome_generico_medicamento->find($id);

        $request->validate([
            'nome' => 'required|string|max:255|unique:nome_generico_medicamentos,nome,'.$id.',id'
        ]);
        
        $input = $request->only(['nome']);

        if (empty($nome_generico_medicamento)) {
            return $this->sendError('Nome Genérico Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $nome_generico_medicamento->update($input);
            DB::commit();
            return $this->sendResponse($nome_generico_medicamento->toArray(), 'Nome Genérico Medicamento updated successfully');

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
        $nome_generico_medicamento = $this->nome_generico_medicamento->find($id);

        if (empty($nome_generico_medicamento)) {
            return $this->sendError('Nome Genérico Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $nome_generico_medicamento->delete();
            DB::commit();
            return $this->sendSuccess('Nome Genérico Medicamento deleted successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
