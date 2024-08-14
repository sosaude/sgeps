<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\FormaMedicamento;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AppBaseController;

class FormaMedicamentoAPIController extends AppBaseController
{
    private $forma_medicamento;
    /**
     * Create a new MarcaMedicamentoAPIController instance.
     *
     * @return void
     */
    public function __construct(FormaMedicamento $forma_medicamento)
    {
        $this->forma_medicamento = $forma_medicamento;

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
        $formas_medicamentos = $this->forma_medicamento->get();

        return $this->sendResponse($formas_medicamentos->toArray(), 'Formas Medicamentos retrieved successfully');
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
        $request->validate(['forma' => 'required|string|max:100']);

        $input = $request->only(['forma']);

        DB::beginTransaction();
        try {

            $forma_medicamento = $this->forma_medicamento->create($input);
            DB::commit();
            return $this->sendResponse($forma_medicamento->toArray(), 'Forma Medicamento saved successfully');
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
        $forma_medicamento = $this->forma_medicamento->find($id);

        if (empty($forma_medicamento)) {
            return $this->sendError('Forma Medicamento not found');
        }

        return $this->sendResponse($forma_medicamento->toArray(), 'Forma Medicamento retrieved successfully');
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
        $forma_medicamento = $this->forma_medicamento->find($id);

        $request->validate(['forma' => 'required|string|max:100']);
        $input = $request->only(['forma']);

        if (empty($forma_medicamento)) {
            return $this->sendError('Marca Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $forma_medicamento->update($input);
            DB::commit();
            return $this->sendResponse($forma_medicamento->toArray(), 'Forma Medicamento updated successfully');

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
        $forma_medicamento = $this->forma_medicamento->find($id);

        if (empty($forma_medicamento)) {
            return $this->sendError('Forma Medicamento not found');
        }

        DB::beginTransaction();
        try {

            $forma_medicamento->delete();
            DB::commit();
            return $this->sendSuccess('Forma Medicamento deleted successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
