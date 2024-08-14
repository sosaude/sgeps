<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateSeccaoAPIRequest;
use App\Http\Requests\API\UpdateSeccaoAPIRequest;
use App\Models\Seccao;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Response;

/**
 * Class SeccaoController
 * @package App\Http\Controllers\API
 */

class SeccaoAPIController extends AppBaseController
{
    /**
     * Display a listing of the Seccao.
     * GET|HEAD /seccaos
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $query = Seccao::query();

        if ($request->get('skip')) {
            $query->skip($request->get('skip'));
        }
        if ($request->get('limit')) {
            $query->limit($request->get('limit'));
        }

        $seccaos = $query->get();

        return $this->sendResponse($seccaos->toArray(), 'Seccaos retrieved successfully');
    }

    /**
     * Store a newly created Seccao in storage.
     * POST /seccaos
     *
     * @param CreateSeccaoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateSeccaoAPIRequest $request)
    {
        $input = $request->all();

        /** @var Seccao $seccao */
        $seccao = Seccao::create($input);

        return $this->sendResponse($seccao->toArray(), 'Seccao saved successfully');
    }

    /**
     * Display the specified Seccao.
     * GET|HEAD /seccaos/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Seccao $seccao */
        $seccao = Seccao::find($id);

        if (empty($seccao)) {
            return $this->sendError('Seccao not found');
        }

        return $this->sendResponse($seccao->toArray(), 'Seccao retrieved successfully');
    }

    /**
     * Update the specified Seccao in storage.
     * PUT/PATCH /seccaos/{id}
     *
     * @param int $id
     * @param UpdateSeccaoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSeccaoAPIRequest $request)
    {
        /** @var Seccao $seccao */
        $seccao = Seccao::find($id);

        if (empty($seccao)) {
            return $this->sendError('Seccao not found');
        }

        $seccao->fill($request->all());
        $seccao->save();

        return $this->sendResponse($seccao->toArray(), 'Seccao updated successfully');
    }

    /**
     * Remove the specified Seccao from storage.
     * DELETE /seccaos/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Seccao $seccao */
        $seccao = Seccao::find($id);

        if (empty($seccao)) {
            return $this->sendError('Seccao not found');
        }

        $seccao->delete();

        return $this->sendSuccess('Seccao deleted successfully');
    }
}
