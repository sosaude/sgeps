<?php

namespace App\Http\Controllers\API;

use Response;
use App\Models\Role;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Permissao;
use Illuminate\Http\Request;
use App\Models\UtilizadorEmpresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUtilizadorEmpresaAPIRequest;
use App\Http\Requests\API\UpdateUtilizadorEmpresaAPIRequest;
use App\Http\Requests\CreateUpdateUtilizadorEmpresaFormRequest;

/**
 * Class UtilizadorEmpresaController
 * @package App\Http\Controllers\API
 */

class UtilizadorEmpresaAPIController extends AppBaseController
{
    private $empresa;
    private $utilizador_empresa;
    private $user;
    private $permissao;
    /**
     * Create a new UtilizadorEmpresaAPIController instance.
     *
     * @return void
     */
    public function __construct(Empresa $empresa, UtilizadorEmpresa $utilizador_empresa, User $user, Permissao $permissao)
    {
        $this->empresa = $empresa;
        $this->utilizador_empresa = $utilizador_empresa;
        $this->user = $user;
        $this->permissao = $permissao;

        // Check if the current user has one of the roles, those are the codigo atribute and not id of the role
        $this->middleware(["CheckRole:1:4"]);
    }

    /**
     * Display a listing of the UtilizadorEmpresa.
     * GET|HEAD /utilizadorEmpresas
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $utilizadores_empresa = $this->utilizador_empresa
                ->byGestorEmpresa()
                ->with('user:id,active', 'user.permissaos:id,nome')
                ->get(['id', 'nome', 'email', 'email_verificado', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id', 'user_id'])
                ->map(function ($utilizador_empresa) {
                    // $utilizador_empresa->activo = $utilizador_empresa->user->active;
                    // return $utilizador_empresa->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id');
                    $utilizador_empresa->permissaos = $utilizador_empresa->user->permissaos;
                    return $utilizador_empresa->only('id', 'nome', 'email', 'email_verificado', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id', 'permissaos');
                });

        return $this->sendResponse($utilizadores_empresa->toArray(), 'Utilizador Empresas retrieved successfully');
    }

    /**
     * Retrieve a listing of resources used to create the UtilizadorEmpresa.
     * GET|HEAD /utilizador_empresa/create
     *
     * @return Response
     */
    public function create()
    {
        $roles = Role::GestorEmpresa()->get();
        $permissaos = $this->permissao
            ->bySeccaoEmpresa()
            ->get(['id', 'nome'])
            ->map( function ($permissao, $key) {
                return [
                    'id' => $permissao->id,
                    'nome' => ucwords($permissao->nome)
                ];
            });

        $data = ['roles' => $roles, 'permissaos' => $permissaos];

        return $this->sendResponse($data, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created UtilizadorEmpresa in storage.
     * POST /utilizador_empresa
     *
     * @param CreateUtilizadorEmpresaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateUtilizadorEmpresaFormRequest $request)
    {
        $input = $request->only(['nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id']);

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

            /** @var UtilizadorFarmacia $utilizador_empresa, $codigo_login */
            $utilizador_empresa = UtilizadorEmpresa::create($input);

            DB::commit();
            return $this->sendResponse($utilizador_empresa->toArray(), 'Utilizador Empresa registado com sucesso');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified UtilizadorEmpresa.
     * GET|HEAD /utilizador_empresa/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var UtilizadorEmpresa $utilizador_empresa */
        // $utilizador_empresa = UtilizadorEmpresa::find($id);
        $utilizador_empresa = $this->utilizador_empresa
            ->with('user:id,active')
            ->find($id, ['id', 'nome', 'contacto', 'email', 'email_verificado', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id', 'user_id']);


        if (empty($utilizador_empresa)) {
            return $this->sendError('Utilizador Empresa not found');
        }

        return $this->sendResponse($utilizador_empresa->toArray(), 'Utilizador Empresa retrieved successfully');
    }

    /**
     * Update the specified UtilizadorEmpresa in storage.
     * PUT/PATCH /utilizador_empresa/{id}
     *
     * @param int $id
     * @param UpdateUtilizadorEmpresaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateUtilizadorEmpresaFormRequest $request)
    {
        $input = $request->only(['nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id']);

        $utilizador_empresa = $this->utilizador_empresa->find($id);

        if (empty($utilizador_empresa)) {
            return $this->sendError('Utilizador Empresa não encontrado.');
        }

        DB::beginTransaction();
        try {
            $utilizador_empresa->update($input);

            $user = $this->user->find($utilizador_empresa->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('Usuário do Utilizador Empresa não encontrado.');
            }

            $user->update(['nome' => $utilizador_empresa->nome, 'active' => $utilizador_empresa->activo, 'role_id' => $utilizador_empresa->role_id]);
            $user->permissaos()->detach();
            $user->permissaos()->attach($request->permissaos);

            DB::commit();
            return $this->sendResponse($utilizador_empresa->toArray(), 'Utilizador Empresa actualizado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified UtilizadorEmpresa from storage.
     * DELETE /utilizador_empresa/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var UtilizadorEmpresa $utilizador_empresa */
        $utilizador_empresa = UtilizadorEmpresa::find($id);

        if (empty($utilizador_empresa)) {
            return $this->sendError('Utilizador Empresa not found');
        }

        DB::beginTransaction();
        try {

            /** @var User $user belongs to utilizador_farmacia being updated*/
            $user = $this->user->find($utilizador_empresa->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('User of Utilizador Empresa not found');
            }

            $utilizador_empresa->delete();
            $user->delete();
            DB::commit();
            return $this->sendSuccess('Utilizador Empresa deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
