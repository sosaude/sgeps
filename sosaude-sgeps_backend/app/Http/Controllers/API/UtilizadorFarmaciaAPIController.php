<?php

namespace App\Http\Controllers\API;

use Response;
use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\Farmacia;
use App\Models\Permissao;
use Illuminate\Http\Request;
use App\Models\UtilizadorFarmacia;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUtilizadorFarmaciaAPIRequest;
use App\Http\Requests\API\UpdateUtilizadorFarmaciaAPIRequest;
use App\Http\Requests\CreateUpdateUtilizadorFarmaciaFormRequest;

/**
 * Class UtilizadorFarmaciaController
 * @package App\Http\Controllers\API
 */

class UtilizadorFarmaciaAPIController extends AppBaseController
{
    private $farmacia;
    private $utilizador_farmacia;
    private $user;
    private $permissao;
    /**
     * Create a new UtilizadorFarmaciaController instance.
     *
     * @return void
     */
    public function __construct(Farmacia $farmacia, UtilizadorFarmacia $utilizador_farmacia, User $user, Permissao $permissao)
    {
        $this->farmacia = $farmacia;
        $this->utilizador_farmacia = $utilizador_farmacia;
        $this->user = $user;
        $this->permissao = $permissao;

        // Check if the current user has one of the roles, those are the codigo atribute and not id of the role
        $this->middleware(["CheckRole:1:2"]);
    }

    /**
     * Display a listing of the UtilizadorFarmacia.
     * GET|HEAD /utilizadorFarmacias
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $utilizadores_farmacia = $this->utilizador_farmacia
        ->with(['user:id,role_id', 'user.permissaos:id,nome', 'user.role:id,codigo,role'])
        ->get()
        ->map(function ($utilizador_farmacia) {

            return [
                'id' => $utilizador_farmacia->id,
                'nome' => $utilizador_farmacia->nome,
                'email' => $utilizador_farmacia->email,
                'email_verificado' => $utilizador_farmacia->email_verificado,
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
        // dd($utilizadorFarmacias);

        return $this->sendResponse($utilizadores_farmacia->toArray(), 'Utilizador Farmacias retrieved successfully');
    }

    /**
     * Retrieve a listing of resources (Farmacia) used to create the UtilizadorFarmacia.
     * GET|HEAD /utilizador_farmacia/create
     *
     * @return Response
     */
    public function create()
    {
        $farmacias = $this->farmacia->select('id', 'nome')->get();
        $roles = Role::bySeccaoFarmacia()->get(['id', 'role']);
        $permissaos = $this->permissao
        ->bySeccaoFarmacia()
        ->get(['id', 'nome'])
        ->map( function ($permissao) {
            return [
                'id' => $permissao->id,
                'nome' => ucwords($permissao->nome)
            ];
        });

        $data = ['farmacias' => $farmacias, 'roles' => $roles, 'permissaos' => $permissaos];

        return $this->sendResponse($data, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created UtilizadorFarmacia in storage.
     * POST /utilizadorFarmacias
     *
     * @param CreateUtilizadorFarmaciaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateUtilizadorFarmaciaFormRequest $request)
    {

        $input = $request->only(['nome', 'email', 'contacto', 'numero_caderneta', 'activo', 'categoria_profissional', 'nacionalidade', 'observacoes', 'farmacia_id', 'role_id']);
// dd($input);
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
            $user->permissaos()->attach($request->permissaos);
            $input['user_id'] = $user->id;

            /** @var UtilizadorFarmacia $utilizador_farmacia */
            $utilizador_farmacia = UtilizadorFarmacia::create($input);

            DB::commit();
            return $this->sendResponse($utilizador_farmacia->toArray(), 'Utilizador Farmacia registado com sucesso.');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified UtilizadorFarmacia.
     * GET|HEAD /utilizadorFarmacias/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var UtilizadorFarmacia $utilizadorFarmacia */
        $utilizador_farmacia = $this->utilizador_farmacia->with('role')->find($id);

        if (empty($utilizador_farmacia)) {
            return $this->sendError('Utilizador Farmacia não encontrado.');
        }

        return $this->sendResponse($utilizador_farmacia->toArray(), 'Utilizador Farmacia retornado com sucesso.');
    }

    /**
     * Update the specified UtilizadorFarmacia in storage.
     * PUT/PATCH /utilizadorFarmacias/{id}
     *
     * @param int $id
     * @param UpdateUtilizadorFarmaciaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateUtilizadorFarmaciaFormRequest $request)
    {
        $input = $request->only(['nome', 'email', 'contacto', 'numero_caderneta', 'activo', 'categoria_profissional', 'nacionalidade', 'observacoes', 'farmacia_id', 'role_id']);
        // dd($input);
        $utilizador_farmacia = UtilizadorFarmacia::find($id);

        if (empty($utilizador_farmacia)) {
            return $this->sendError('Utilizador Farmacia não encontrado.');
        }


        DB::beginTransaction();
        try {
            $utilizador_farmacia->update($input);

            $user = $this->user->find($utilizador_farmacia->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('Usuário do Utilizador Farmacia não encontrado.');
            }

            $user->update(['nome' => $utilizador_farmacia->nome, 'active' => $utilizador_farmacia->activo, 'role_id' => $utilizador_farmacia->role_id]);
            $user->permissaos()->detach();
            $user->permissaos()->attach($request->permissaos);

            DB::commit();
            return $this->sendResponse($utilizador_farmacia->toArray(), 'UtilizadorFarmacia actualizada com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified UtilizadorFarmacia from storage.
     * DELETE /utilizadorFarmacias/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var UtilizadorFarmacia $utilizador_farmacia */
        $utilizador_farmacia = UtilizadorFarmacia::find($id);

        if (empty($utilizador_farmacia)) {
            return $this->sendError('Utilizador Farmacia not found');
        }

        DB::beginTransaction();
        try {

            /** @var User $user belongs to utilizador_farmacia being updated*/
            $user = $this->user->find($utilizador_farmacia->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('User of Utilizador Farmacia not found');
            }

            $utilizador_farmacia->delete();
            $user->delete();
            DB::commit();
            return $this->sendSuccess('Utilizador Farmacia deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
