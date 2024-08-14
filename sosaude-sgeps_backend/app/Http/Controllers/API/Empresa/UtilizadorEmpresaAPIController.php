<?php

namespace App\Http\Controllers\API\Empresa;

use App\Models\Role;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Permissao;
use Illuminate\Http\Request;
use App\Models\UtilizadorEmpresa;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUtilizadorEmpresaAPIRequest;
use App\Http\Requests\API\UpdateUtilizadorEmpresaAPIRequest;
use App\Http\Requests\CreateUpdateUtilizadorEmpresaFormRequest;

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

        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = $request->empresa_id;
        $utilizadores_empresa = $this->utilizador_empresa
            ->byEmpresa($empresa_id)
            ->with('user:id,active', 'user.permissaos:id,nome')
            ->get()
            ->map(function ($utilizador_empresa) {
                // $utilizador_empresa->activo = $utilizador_empresa->user->active;
                $utilizador_empresa->permissaos = $utilizador_empresa->user->permissaos;
                return $utilizador_empresa->only('id', 'nome', 'email', 'email_verificado', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'role_id', 'permissaos');
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
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $roles = Role::byUtilizadoresEmpresa()->get(['id', 'codigo', 'role']);
        $permissaos = $this->permissao
            ->bySeccaoEmpresa()
            ->get(['id', 'nome'])
            ->map(function ($permissao, $key) {
                // return ucwords($permissao->nome);
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
     * @param CreateUpdateUtilizadorEmpresaFormRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateUtilizadorEmpresaFormRequest $request)
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $input = $request->all();
        $input = $request->only(['nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id', 'empresa_id']);

        DB::beginTransaction();
        try {

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

            $utilizador_empresa = UtilizadorEmpresa::create($input);

            $data = [
                'id' => $utilizador_empresa->id,
                'nome' => $utilizador_empresa->nome,
                'contacto' => $utilizador_empresa->contacto,
                'activo' => $user->active ? 1 : 0,
                'nacionalidade' => $utilizador_empresa->nacionalidade,
                'observacoes' => $utilizador_empresa->observacoes,
                'role_id' => $utilizador_empresa->role_id,
                'permissaos' => $request->permissaos,
            ];

            DB::commit();
            return $this->sendResponse($data, 'Utilizador Empresa registado com sucesso.');
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

        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $empresa_id = request('empresa_id');
        /** @var UtilizadorEmpresa $utilizador_empresa */
        /* $utilizador_empresa = UtilizadorEmpresa::with('user:id,active')
            ->where('id', $id)
            ->get(['id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'role_id', 'user_id']); */

        $utilizador_empresa = UtilizadorEmpresa::with('user:id,active')
            ->byEmpresa($empresa_id)
            ->where('id', $id)
            ->get(['id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'role_id', 'user_id'])
            ->map(function ($utilizador_empresa) {
                // $utilizador_empresa->activo = $utilizador_empresa->user->active;
                return $utilizador_empresa->only('id', 'nome', 'email', 'email_verificado', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'role_id');
            });

        if (empty($utilizador_empresa)) {
            return $this->sendError('Utilizador Empresa não encontrado.');
        }

        return $this->sendResponse($utilizador_empresa->toArray(), 'Utilizador Empresa retornado com sucesso.');
    }

    /**
     * Update the specified UtilizadorEmpresa in storage.
     * PUT/PATCH /utilizador_empresa/{id}
     *
     * @param int $id
     * @param CreateUpdateUtilizadorEmpresaFormRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateUtilizadorEmpresaFormRequest $request)
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');
        $input = $request->only(['nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id', 'empresa_id']);

        $utilizador_empresa = UtilizadorEmpresa::byEmpresa($empresa_id)->find($id);
        if (empty($utilizador_empresa))
            return $this->sendError('Utilizador Empresa não encontrado');

        $user = $this->user->find($utilizador_empresa->user_id);
        if (empty($user))
            return $this->sendError('User of Utilizador Empresa não encontrado.');

        DB::beginTransaction();
        try {
            $utilizador_empresa->update($input);



            $user->update(['nome' => $utilizador_empresa->nome, 'active' => $utilizador_empresa->activo, 'role_id' => $utilizador_empresa->role_id]);
            $user->permissaos()->detach();
            $user->permissaos()->attach($request->permissaos);

            DB::commit();
            return $this->sendResponse($utilizador_empresa->toArray(), 'UtilizadorEmpresa actualizado com sucesso.');
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
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');

        $utilizador_empresa = UtilizadorEmpresa::byEmpresa($empresa_id)->find($id);
        if (empty($utilizador_empresa))
            return $this->sendError('Utilizador Empresa não encontrado.');

        $user = $this->user->find($utilizador_empresa->user_id);
        if (empty($user))
            return $this->sendError('Usuário do Utilizador Empresa não encontrado.');

        DB::beginTransaction();
        try {
            $utilizador_empresa->delete();
            $user->delete();
            DB::commit();
            return $this->sendSuccess('Utilizador Empresa removido com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
