<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateCategoriaEmpresaAPIRequest;
use App\Http\Requests\API\UpdateCategoriaEmpresaAPIRequest;
use App\Models\CategoriaEmpresa;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class CategoriaEmpresaController
 * @package App\Http\Controllers\API
 */

class CategoriaEmpresaAPIController extends AppBaseController
{
    /**
     * Display a listing of the CategoriaEmpresa.
     * GET|HEAD /categoriaEmpresas
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $query = CategoriaEmpresa::query();

        if ($request->get('skip')) {
            $query->skip($request->get('skip'));
        }
        if ($request->get('limit')) {
            $query->limit($request->get('limit'));
        }

        $categoriaEmpresas = $query->get();

        return $this->sendResponse($categoriaEmpresas->toArray(), 'Categoria Empresas retrieved successfully');
    }

    /**
     * Store a newly created CategoriaEmpresa in storage.
     * POST /categoriaEmpresas
     *
     * @param CreateCategoriaEmpresaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateCategoriaEmpresaAPIRequest $request)
    {
        $input = $request->all();

        /** @var CategoriaEmpresa $categoriaEmpresa */
        $categoriaEmpresa = CategoriaEmpresa::create($input);

        return $this->sendResponse($categoriaEmpresa->toArray(), 'Categoria Empresa saved successfully');
    }

    /**
     * Display the specified CategoriaEmpresa.
     * GET|HEAD /categoriaEmpresas/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var CategoriaEmpresa $categoriaEmpresa */
        $categoriaEmpresa = CategoriaEmpresa::find($id);

        if (empty($categoriaEmpresa)) {
            return $this->sendError('Categoria Empresa not found');
        }

        return $this->sendResponse($categoriaEmpresa->toArray(), 'Categoria Empresa retrieved successfully');
    }

    /**
     * Update the specified CategoriaEmpresa in storage.
     * PUT/PATCH /categoriaEmpresas/{id}
     *
     * @param int $id
     * @param UpdateCategoriaEmpresaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCategoriaEmpresaAPIRequest $request)
    {
        /** @var CategoriaEmpresa $categoriaEmpresa */
        $categoriaEmpresa = CategoriaEmpresa::find($id);

        if (empty($categoriaEmpresa)) {
            return $this->sendError('Categoria Empresa not found');
        }

        $categoriaEmpresa->fill($request->all());
        $categoriaEmpresa->save();

        return $this->sendResponse($categoriaEmpresa->toArray(), 'CategoriaEmpresa updated successfully');
    }

    /**
     * Remove the specified CategoriaEmpresa from storage.
     * DELETE /categoriaEmpresas/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var CategoriaEmpresa $categoriaEmpresa */
        $categoriaEmpresa = CategoriaEmpresa::find($id);

        if (empty($categoriaEmpresa)) {
            return $this->sendError('Categoria Empresa not found');
        }

        $categoriaEmpresa->delete();

        return $this->sendSuccess('Categoria Empresa deleted successfully');
    }
}
