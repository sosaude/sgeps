<?php

namespace App\Http\Controllers\API;

use Response;
use App\Models\Tenant;
use App\Models\Clinica;
use Illuminate\Http\Request;
use App\Models\UtilizadorClinica;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateClinicaAPIRequest;
use App\Http\Requests\API\UpdateClinicaAPIRequest;

/**
 * Class ClinicaController
 * @package App\Http\Controllers\API
 */

class ClinicaAPIController extends AppBaseController
{
    private $clinica;
    private $utilizador_clinica;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(Clinica $clinica, UtilizadorClinica $utilizador_clinica)
    {
        $this->middleware(["CheckRole:1"]);
        $this->clinica = $clinica;
        $this->utilizador_clinica = $utilizador_clinica;
    }
    /**
     * Display a listing of the Clinica.
     * GET|HEAD /clinicas
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $clinicas = $this->clinica->orderBy('nome', 'asc')->get();

        return $this->sendResponse($clinicas->toArray(), 'Clinicas retrieved successfully');
    }

    /**
     * Store a newly created Clinica in storage.
     * POST /clinicas
     *
     * @param CreateClinicaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateClinicaAPIRequest $request)
    {

        DB::beginTransaction();
        try {
            $tenant = new Tenant();
            $tenant->nome = $request->nome;
            $tenant->save();

            $input = [
                "nome" => $request->nome,
                "endereco" => $request->endereco,
                "email" => $request->email,
                "nuit" => $request->nuit,
                "contactos" => $request->contactos,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'tenant_id' => $tenant->id,
            ];

            /** @var Clinica $clinica */
            $clinica = Clinica::create($input);
            DB::commit();
            return $this->sendResponse($clinica->toArray(), 'Clinica saved successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified Clinica.
     * GET|HEAD /clinicas/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Clinica $clinica */
        $clinica = Clinica::find($id);

        if (empty($clinica)) {
            return $this->sendError('Empresa not found');
        }

        return $this->sendResponse($clinica->toArray(), 'Clinica retrieved successfully');
    }

    /**
     * Update the specified Clinica in storage.
     * PUT/PATCH /clinicas/{id}
     *
     * @param int $id
     * @param UpdateClinicaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateClinicaAPIRequest $request)
    {

        $input = [
            "nome" => $request->nome,
            "endereco" => $request->endereco,
            "email" => $request->email,
            "nuit" => $request->nuit,
            "contactos" => $request->contactos,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        /** @var Clinica $clinica */
        $clinica = Clinica::find($id);

        if (empty($clinica)) {
            return $this->sendError('Clinica not found');
        }

        DB::beginTransaction();
        try {
            $clinica->fill($input);
            $clinica->save();

            $tenant = Tenant::find($clinica->tenant_id);
            $tenant->nome = $clinica->nome;
            $tenant->save();
            DB::commit();
            return $this->sendResponse($clinica->toArray(), 'Clinica updated successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified Clinica from storage.
     * DELETE /clinicas/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Clinica $clinica */
        $clinica = Clinica::with('utilizadoresClinica')->find($id);
        // $tenant = Tenant::find($clinica->tenant_id);

        if (empty($clinica)) {
            return $this->sendError('Clinica not found');
        }
       /*  if (empty($tenant)) {
            return $this->sendError('Tenant not found');
        } */

        DB::beginTransaction();
        try {
            // $clinica->utilizadoresClinica()->delete();
            // $users_id = $clinica->utilizadoresClinica->pluck('user_id');
            // User::whereIn($users_id)->update(['active' => false]);
            // $clinica->utilizadoresClinica()->delete();
            foreach($clinica->utilizadoresClinica as $utilizador_clinica) {
                $utilizador_clinica->delete();
            }
            // User::whereIn($users_id)->delete();
            
            // dd($utilizadores);
            $clinica->delete();
            // $tenant->delete();
            DB::commit();
            return $this->sendSuccess('Clinica deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Retrieve the utilizador_farmacia oh the Farmacia from storage.
     * GET /clinicas/utilizadores/{id}
     *
     * @param int $id
     *
     *
     * @return Response
     */
    public function utilizadores($clinica_id)
    {
        $authenticated = Auth::user();

        if ($authenticated->role->codigo == 1) {
            $utilizadores_clinica = $this->utilizador_clinica->where('clinica_id', $clinica_id)->byGestorClinica()->get();
        } else {
            $utilizadores_clinica = $this->utilizador_clinica->where('clinica_id', $clinica_id)->get();
        }

        return $this->sendResponse($utilizadores_clinica->toArray(), 'Utilizadores Clinica retrieved successfully');
    }
}
