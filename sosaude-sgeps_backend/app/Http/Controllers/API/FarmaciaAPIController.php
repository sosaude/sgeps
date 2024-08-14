<?php

namespace App\Http\Controllers\API;

use Response;
use App\Models\Tenant;
use App\Models\Farmacia;
use Illuminate\Http\Request;
use App\Models\UtilizadorFarmacia;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateFarmaciaAPIRequest;
use App\Http\Requests\API\UpdateFarmaciaAPIRequest;
use App\Http\Requests\API\CreateUpdateFarmaciaFormRequest;

/**
 * Class FarmaciaController
 * @package App\Http\Controllers\API
 */

class FarmaciaAPIController extends AppBaseController
{
    private $farmacia;
    private $utilizador_farmacia;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(Farmacia $farmacia, UtilizadorFarmacia $utilizador_farmacia)
    {
        $this->middleware(["CheckRole:1"]);
        $this->farmacia = $farmacia;
        $this->utilizador_farmacia = $utilizador_farmacia;
    }

    /**
     * Display a listing of the Farmacia.
     * GET|HEAD /farmacias
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $query = Farmacia::query();

        if ($request->get('skip')) {
            $query->skip($request->get('skip'));
        }
        if ($request->get('limit')) {
            $query->limit($request->get('limit'));
        }

        $farmacias = $query->get();

        return $this->sendResponse($farmacias->toArray(), 'Farmacias retrieved successfully');
    }

    

    /**
     * Store a newly created Farmacia in storage.
     * POST /farmacias
     *
     * @param CreateFarmaciaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateFarmaciaFormRequest $request)
    {
        
        
        // $request->validate(['latitude' => 'required|numeric', 'longitude' => 'required|numeric']);
        // dd($request->all());
        DB::beginTransaction();
        try {

            $tenant = new Tenant();
            $tenant->nome = $request->nome;
            $tenant->save();

            $input = [
                "nome" => $request->nome,
                "email" => $request->email,
                "endereco" => $request->endereco,
                "horario_funcionamento" => $request->horario_funcionamento,
                "activa" => $request->activa,
                "contactos" => $request->contactos,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                "numero_alvara" => $request->numero_alvara,
                "data_alvara_emissao" => $request->data_alvara_emissao,
                "observacoes" => $request->observacoes,
                "tenant_id" => $tenant->id
            ];

            /** @var Farmacia $farmacia */
            $farmacia = Farmacia::create($input);
            DB::commit();
            return $this->sendResponse($farmacia->toArray(), 'Farmacia saved successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

    }

    /**
     * Display the specified Farmacia.
     * GET|HEAD /farmacias/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Farmacia $farmacia */
        $farmacia = Farmacia::find($id);

        if (empty($farmacia)) {
            return $this->sendError('Farmacia not found');
        }
        return $this->sendResponse($farmacia->toArray(), 'Farmacia retrieved successfully');
    }

    /**
     * Update the specified Farmacia in storage.
     * PUT/PATCH /farmacias/{id}
     *
     * @param int $id
     * @param UpdateFarmaciaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateFarmaciaFormRequest $request)
    {
        /** @var Farmacia $farmacia */
        $farmacia = Farmacia::find($id);
        $input = [
            "nome" => $request->nome,
            "email" => $request->email,
            "endereco" => $request->endereco,
            "horario_funcionamento" => $request->horario_funcionamento,
            "activa" => $request->activa,
            "contactos" => $request->contactos,
            "latitude" => $request->latitude,
            "longitude" => $request->longitude,
            "numero_alvara" => $request->numero_alvara,
            "data_alvara_emissao" => $request->data_alvara_emissao,
            "observacoes" => $request->observacoes,
        ];
// dd($request->all());
        if (empty($farmacia)) {
            return $this->sendError('Farmacia not found');
        }

        DB::beginTransaction();
        try {
            $farmacia->fill($input);
            $farmacia->save();

            $tenant = Tenant::find($farmacia->tenant_id);
            $tenant->nome = $farmacia->nome;
            $tenant->save();

            DB::commit();
            return $this->sendResponse($farmacia->toArray(), 'Farmacia updated successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

    }

    /**
     * Retrieve the utilizador_farmacia oh the Farmacia from storage.
     * GET /farmacias/utilizadores/{id}
     *
     * @param int $id
     *
     *
     * @return Response
     */
    public function utilizadores($id)
    {
        // $utilizadores_farmacia = $this->utilizador_farmacia->where('farmacia_id', $id)->get();
        $utilizadores_farmacia = $this->utilizador_farmacia
        ->with(['user:id,role_id', 'user.permissaos:id,nome', 'user.role:id,codigo,role'])
        ->where('farmacia_id', $id)
        ->get()
        ->map(function ($utilizador_farmacia) {

            return [
                'id' => $utilizador_farmacia->id,
                'nome' => $utilizador_farmacia->nome,
                'email' => $utilizador_farmacia->email,
                'contacto' => $utilizador_farmacia->contacto,
                'numero_caderneta' => $utilizador_farmacia->numero_caderneta,
                'activo' => $utilizador_farmacia->activo,
                'categoria_profissional' => $utilizador_farmacia->categoria_profissional,
                'nacionalidade' => $utilizador_farmacia->nacionalidade,
                'observacoes' => $utilizador_farmacia->observacoes,
                'farmacia_id' => $utilizador_farmacia->farmacia_id,
                'created_at' => !empty($utilizador_farmacia->created_at) ? date('Y-m-d H:m:s', strtotime($utilizador_farmacia->created_at)) : null,
                'updated_at' => !empty($utilizador_farmacia->updated_at) ? date('Y-m-d H:m:s', strtotime($utilizador_farmacia->updated_at)) : null,
                'deleted_at' => !empty($utilizador_farmacia->deleted_at) ? date('Y-m-d H:m:s', strtotime($utilizador_farmacia->deleted_at)) : null,
                'role_id' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->id : '',
                // 'role_codigo' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->codigo : '',
                // 'role_nome' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->role : '',
                'permissaos' => !empty($utilizador_farmacia->user->permissaos) ? $utilizador_farmacia->user->permissaos : '',
            ];
        });
        
        return $this->sendResponse($utilizadores_farmacia->toArray(), 'Farmacia updated successfully');
    }

    /**
     * Remove the specified Farmacia from storage.
     * DELETE /farmacias/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Farmacia $farmacia, Tenant $tenant */
        $farmacia = Farmacia::with('utilizadorFarmacias')->find($id);

        if (empty($farmacia)) {
            return $this->sendError('Farmacia not found');
        }

        DB::beginTransaction();
        try {

           /*  foreach($farmacia->utilizadorFarmacias as $utilizador_farmacia) {
                $utilizador_farmacia->delete();
            } */

            $farmacia->delete();
            DB::commit();
            return $this->sendSuccess('Farmacia deleted successfully');
            
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        } catch (QueryException $e) {
            DB::rollback();
            return $this->sendError('Erro de integridade referêncial na Base de Dados! Contacte o Administrador.', 500);
        }

    }

    public function teste()
    {
        dd("teste");
    }
}
