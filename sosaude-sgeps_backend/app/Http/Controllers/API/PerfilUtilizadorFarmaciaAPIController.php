<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreatePerfilUtilizadorFarmaciaAPIRequest;
use App\Http\Requests\API\UpdatePerfilUtilizadorFarmaciaAPIRequest;
use App\Models\PerfilUtilizadorFarmacia;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class PerfilUtilizadorFarmaciaController
 * @package App\Http\Controllers\API
 */

class PerfilUtilizadorFarmaciaAPIController extends AppBaseController
{
    /**
     * Display a listing of the PerfilUtilizadorFarmacia.
     * GET|HEAD /perfilUtilizadorFarmacias
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $query = PerfilUtilizadorFarmacia::query();

        if ($request->get('skip')) {
            $query->skip($request->get('skip'));
        }
        if ($request->get('limit')) {
            $query->limit($request->get('limit'));
        }

        $perfilUtilizadorFarmacias = $query->get();

        return $this->sendResponse($perfilUtilizadorFarmacias->toArray(), 'Perfil Utilizador Farmacias retrieved successfully');
    }

    /**
     * Store a newly created PerfilUtilizadorFarmacia in storage.
     * POST /perfilUtilizadorFarmacias
     *
     * @param CreatePerfilUtilizadorFarmaciaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreatePerfilUtilizadorFarmaciaAPIRequest $request)
    {
        $input = $request->all();

        /** @var PerfilUtilizadorFarmacia $perfilUtilizadorFarmacia */
        $perfilUtilizadorFarmacia = PerfilUtilizadorFarmacia::create($input);

        return $this->sendResponse($perfilUtilizadorFarmacia->toArray(), 'Perfil Utilizador Farmacia saved successfully');
    }

    /**
     * Display the specified PerfilUtilizadorFarmacia.
     * GET|HEAD /perfilUtilizadorFarmacias/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var PerfilUtilizadorFarmacia $perfilUtilizadorFarmacia */
        $perfilUtilizadorFarmacia = PerfilUtilizadorFarmacia::find($id);

        if (empty($perfilUtilizadorFarmacia)) {
            return $this->sendError('Perfil Utilizador Farmacia not found');
        }

        return $this->sendResponse($perfilUtilizadorFarmacia->toArray(), 'Perfil Utilizador Farmacia retrieved successfully');
    }

    /**
     * Update the specified PerfilUtilizadorFarmacia in storage.
     * PUT/PATCH /perfilUtilizadorFarmacias/{id}
     *
     * @param int $id
     * @param UpdatePerfilUtilizadorFarmaciaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdatePerfilUtilizadorFarmaciaAPIRequest $request)
    {
        /** @var PerfilUtilizadorFarmacia $perfilUtilizadorFarmacia */
        $perfilUtilizadorFarmacia = PerfilUtilizadorFarmacia::find($id);

        if (empty($perfilUtilizadorFarmacia)) {
            return $this->sendError('Perfil Utilizador Farmacia not found');
        }

        $perfilUtilizadorFarmacia->fill($request->all());
        $perfilUtilizadorFarmacia->save();

        return $this->sendResponse($perfilUtilizadorFarmacia->toArray(), 'PerfilUtilizadorFarmacia updated successfully');
    }

    /**
     * Remove the specified PerfilUtilizadorFarmacia from storage.
     * DELETE /perfilUtilizadorFarmacias/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var PerfilUtilizadorFarmacia $perfilUtilizadorFarmacia */
        $perfilUtilizadorFarmacia = PerfilUtilizadorFarmacia::find($id);

        if (empty($perfilUtilizadorFarmacia)) {
            return $this->sendError('Perfil Utilizador Farmacia not found');
        }

        $perfilUtilizadorFarmacia->delete();

        return $this->sendSuccess('Perfil Utilizador Farmacia deleted successfully');
    }
}
