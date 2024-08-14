<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateFormaMarcaMedicamentoAPIRequest;
use App\Http\Requests\API\UpdateFormaMarcaMedicamentoAPIRequest;
use App\Models\FormaMarcaMedicamento;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Models\FormaMedicamento;
use Response;

/**
 * Class FormaMarcaMedicamentoController
 * @package App\Http\Controllers\API
 */

class FormaMarcaMedicamentoAPIController extends AppBaseController
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
     * Display a listing of the FormaMarcaMedicamento.
     * GET|HEAD /formaMarcaMedicamentos
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $formas_medicamento = $this->forma_medicamento->all();

        return $this->sendResponse($formas_medicamento->toArray(), 'Forma Marca Medicamentos retrieved successfully');
    }

    /**
     * Store a newly created FormaMarcaMedicamento in storage.
     * POST /formaMarcaMedicamentos
     *
     * @param CreateFormaMarcaMedicamentoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateFormaMarcaMedicamentoAPIRequest $request)
    {
        $input = $request->all();

        /** @var FormaMarcaMedicamento $formaMarcaMedicamento */
        $formaMarcaMedicamento = FormaMarcaMedicamento::create($input);

        return $this->sendResponse($formaMarcaMedicamento->toArray(), 'Forma Marca Medicamento saved successfully');
    }

    /**
     * Display the specified FormaMarcaMedicamento.
     * GET|HEAD /formaMarcaMedicamentos/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var FormaMarcaMedicamento $formaMarcaMedicamento */
        $formaMarcaMedicamento = FormaMarcaMedicamento::find($id);

        if (empty($formaMarcaMedicamento)) {
            return $this->sendError('Forma Marca Medicamento not found');
        }

        return $this->sendResponse($formaMarcaMedicamento->toArray(), 'Forma Marca Medicamento retrieved successfully');
    }

    /**
     * Update the specified FormaMarcaMedicamento in storage.
     * PUT/PATCH /formaMarcaMedicamentos/{id}
     *
     * @param int $id
     * @param UpdateFormaMarcaMedicamentoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateFormaMarcaMedicamentoAPIRequest $request)
    {
        /** @var FormaMarcaMedicamento $formaMarcaMedicamento */
        $formaMarcaMedicamento = FormaMarcaMedicamento::find($id);

        if (empty($formaMarcaMedicamento)) {
            return $this->sendError('Forma Marca Medicamento not found');
        }

        $formaMarcaMedicamento->fill($request->all());
        $formaMarcaMedicamento->save();

        return $this->sendResponse($formaMarcaMedicamento->toArray(), 'FormaMarcaMedicamento updated successfully');
    }

    /**
     * Remove the specified FormaMarcaMedicamento from storage.
     * DELETE /formaMarcaMedicamentos/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var FormaMarcaMedicamento $formaMarcaMedicamento */
        $formaMarcaMedicamento = FormaMarcaMedicamento::find($id);

        if (empty($formaMarcaMedicamento)) {
            return $this->sendError('Forma Marca Medicamento not found');
        }

        $formaMarcaMedicamento->delete();

        return $this->sendSuccess('Forma Marca Medicamento deleted successfully');
    }
}
