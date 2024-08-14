<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\CategoriaServico;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateCategoriaServicoFormRequest;

class CategoriaServicoAPIController extends AppBaseController
{
    private $categoria_servico;
    /**
     * Create a new MarcaMedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(CategoriaServico $categoria_servico)
    {
        $this->categoria_servico = $categoria_servico;

        // Check if the current user has one of the roles, those are the codigo atribute and not id of the role
        $this->middleware(["CheckRole:1"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorias_servicos = $this->categoria_servico->get(['id', 'codigo', 'nome']);

        $data = [
            'categorias_servicos' => $categorias_servicos
        ];

        return $this->sendResponse($data, 'Servicos retrieved successfully');
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
    public function store(CreateUpdateCategoriaServicoFormRequest $request)
    {
        $input = $request->only(['codigo', 'nome']);
        DB::beginTransaction();
        try {

            $categoria_servico = $this->categoria_servico->create($input);
            DB::commit();
            return $this->sendResponse($categoria_servico->toArray(), 'Categoria de Serviço criada com sucesso!');
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
        $categoria_servico = $this->categoria_servico->find($id);

        if (empty($categoria_servico)) {
            return $this->sendError('Categoria de Serviço não encontrada!');
        }

        return $this->sendResponse($categoria_servico->toArray(), '');
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
    public function update(CreateUpdateCategoriaServicoFormRequest $request, $id)
    {
        $categoria_servico = $this->categoria_servico->find($id);
        
        $input = $request->only(['codigo', 'nome']);

        if (empty($categoria_servico)) {
            return $this->sendError('Categoria de Serviço não encontrada!');
        }

        DB::beginTransaction();
        try {

            $categoria_servico->update($input);
            DB::commit();
            return $this->sendResponse($categoria_servico->toArray(), 'Categoria Serviço actualizada com sucesso!');

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
        $categoria_servico = $this->categoria_servico->find($id);

        if (empty($categoria_servico)) {
            return $this->sendError('Categoria de Serviço não encontrada!');
        }

        DB::beginTransaction();
        try {

            $categoria_servico->delete();
            DB::commit();
            return $this->sendSuccess('Categoria Serviço removida com sucesso!');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
