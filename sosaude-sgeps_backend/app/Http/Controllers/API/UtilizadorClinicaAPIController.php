<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUtilizadorClinicaAPIRequest;
use App\Http\Requests\API\UpdateUtilizadorClinicaAPIRequest;
use App\Models\Clinica;
use App\Models\Role;
use App\Models\User;
use App\Models\UtilizadorClinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Response;

/**
 * Class UtilizadorClinicaController
 * @package App\Http\Controllers\API
 */

class UtilizadorClinicaAPIController extends AppBaseController
{
    private $clinica;
    private $utilizador_clinica;
    private $user;

    /**
     * Create a new UtilizadorClinicaAPIController instance.
     *
     * @return void
     */
    public function __construct(Clinica $clinica, UtilizadorClinica $utilizador_clinica, User $user)
    {
        $this->clinica = $clinica;
        $this->utilizador_clinica = $utilizador_clinica;
        $this->user = $user;

        // Check if the current user has one of the roles. Those are the codigo atribute and not id of the role
        $this->middleware(["CheckRole:1:6"]);
    }

    /**
     * Display a listing of the UtilizadorClinica.
     * GET|HEAD /utilizador_clinica
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $authenticated = Auth::user();

        if ($authenticated->role->codigo == 1) {
            $utilizadores_clinica = $this->utilizador_clinica->byGestorClinica()->get();
        } else {
            $utilizadores_clinica = $this->utilizador_clinica->all();
        }

        return $this->sendResponse($utilizadores_clinica->toArray(), 'Utilizador Clinica retrieved successfully');
    }

    /**
     * Retrieve a listing of resources used to create the UtilizadorEmpresa.
     * GET|HEAD /utilizador_clinica/create
     *
     * @return Response
     */
    public function create()
    {
        $authenticated = Auth::user();
        $roles = null;

        if ($authenticated->role->codigo == 1) {
            $roles = Role::GestorClinica()->get();
        } else {
            $roles = Role::bySeccaoClinica()->get();
        }

        $data = ['roles' => $roles];

        return $this->sendResponse($data, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created UtilizadorClinica in storage.
     * POST /utilizador_clinica
     *
     * @param CreateUtilizadorClinicaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUtilizadorClinicaAPIRequest $request)
    {

        $input = $request->all();

        DB::beginTransaction();
        try {

            /** @var User $user */
            $user = new User();
            $user->nome = $request->nome;
            $user->password = bcrypt('1234567'); // Default password, is changed after the created Event of Beneficiario
            $user->active = $request->activo;
            $user->loged_once = 0;
            $user->login_attempts = 0;
            $user->role_id = $request->role_id;
            $user->save();
            $input['user_id'] = $user->id;
            
            /** @var UtilizadorClinica $utilizador_clinica */
            $utilizador_clinica = UtilizadorClinica::create($input);

            DB::commit();
            return $this->sendResponse($utilizador_clinica->toArray(), 'Utilizador Clinica saved successfully');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified UtilizadorClinica.
     * GET|HEAD /utilizador_clinica/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var UtilizadorClinica $utilizador_clinica */
        $utilizador_clinica = UtilizadorClinica::find($id);

        if (empty($utilizador_clinica)) {
            return $this->sendError('Utilizador Clinica not found');
        }

        return $this->sendResponse($utilizador_clinica->toArray(), 'Utilizador Clinica retrieved successfully');
    }

    /**
     * Update the specified UtilizadorClinica in storage.
     * PUT/PATCH /utilizador_clinica/{id}
     *
     * @param int $id
     * @param UpdateUtilizadorClinicaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateUtilizadorClinicaAPIRequest $request)
    {
        /** @var UtilizadorClinica $utilizador_linica */
        $utilizador_clinica = UtilizadorClinica::find($id);

        if (empty($utilizador_clinica)) {
            return $this->sendError('Utilizador Clinica not found');
        }

        $utilizador_clinica->fill($request->all());

        DB::beginTransaction();
        try {
            $utilizador_clinica->save();

            /** @var User $user belongs to utilizador_clinica being updated*/
            $user = $this->user->find($utilizador_clinica->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('User of Utilizador Clinica not found');
            }

            $user->update(['nome' => $utilizador_clinica->nome, 'active' => $utilizador_clinica->activo, 'role_id' => $utilizador_clinica->role_id]);

            DB::commit();
            return $this->sendResponse($utilizador_clinica->toArray(), 'Utilizador Clinica updated successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified UtilizadorClinica from storage.
     * DELETE /utilizador_clinica/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var UtilizadorClinica $utilizador_clinica */
        $utilizador_clinica = UtilizadorClinica::find($id);

        if (empty($utilizador_clinica)) {
            return $this->sendError('Utilizador Clinica not found');
        }

        DB::beginTransaction();
        try {

            /** @var User $user belongs to utilizador_clinica being updated*/
            $user = $this->user->find($utilizador_clinica->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('User of Utilizador Clinica not found');
            }

            $utilizador_clinica->delete();
            $user->delete();
            DB::commit();
            return $this->sendSuccess('Utilizador Clinica deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
