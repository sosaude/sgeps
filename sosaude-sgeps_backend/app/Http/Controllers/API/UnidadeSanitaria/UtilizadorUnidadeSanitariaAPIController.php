<?php

namespace App\Http\Controllers\API\UnidadeSanitaria;

use App\Models\Role;
use App\Models\User;
use App\Models\Permissao;
use Illuminate\Http\Request;
use App\Models\UnidadeSanitaria;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\UtilizadorUnidadeSanitaria;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateUtilizadorUnidadeSanitariaFormRequest;

class UtilizadorUnidadeSanitariaAPIController extends AppBaseController
{

    private $unidade_sanitaria;
    private $utilizador_unidade_sanitaria;
    private $user;
    private $permissao;

    /**
     * Create a new UtilizadorUnidadeSanitariaAPIController instance.
     *
     * @return void
     */
    public function __construct(UnidadeSanitaria $unidade_sanitaria, UtilizadorUnidadeSanitaria $utilizador_unidade_sanitaria, User $user, Permissao $permissao)
    {
        $this->unidade_sanitaria = $unidade_sanitaria;
        $this->utilizador_unidade_sanitaria = $utilizador_unidade_sanitaria;
        $this->user = $user;
        $this->permissao = $permissao;

        // Check if the current user has one of the roles. Those are the codigo atribute and not id of the role
        // $this->middleware(["CheckRole:1:6"]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria
            ->byUnidadeSanitaria($unidade_sanitaria_id)
            ->with('user:id,active,role_id', 'user.permissaos:id,nome', 'user.role:id,codigo,role')
            ->get(['id', 'nome', 'email', 'email_verificado', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'role_id', 'user_id'])
            ->map(function ($utilizador_unidade_sanitaria) {
                // $utilizador_unidade_sanitaria->activo = $utilizador_unidade_sanitaria->user->active;

                return [
                    'id' => $utilizador_unidade_sanitaria->id,
                    'nome' => $utilizador_unidade_sanitaria->nome,
                    'email' => $utilizador_unidade_sanitaria->email,
                    'email_verificado' => $utilizador_unidade_sanitaria->email_verificado,
                    'contacto' => $utilizador_unidade_sanitaria->contacto,
                    'activo' => $utilizador_unidade_sanitaria->activo,
                    'nacionalidade' => $utilizador_unidade_sanitaria->nacionalidade,
                    'observacoes' => $utilizador_unidade_sanitaria->observacoes,
                    'role_id' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->id : '',
                    'role_codigo' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->codigo : '',
                    'role_nome' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->role : '',
                    'permissaos' => !empty($utilizador_unidade_sanitaria->user->permissaos) ? $utilizador_unidade_sanitaria->user->permissaos : '',
                ];
                // return $utilizador_unidade_sanitaria->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'role');
            });

        return $this->sendResponse($utilizadores_unidade_sanitaria->toArray(), 'Utilizador Unidade Sanitária retrieved successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $roles = Role::bySeccaoUnidadeSanitaria()->get();
        $permissaos = $this->permissao
            ->bySeccaoUnidadeSanitaria()
            ->get(['id', 'nome'])
            ->map(function ($permissao) {
                return [
                    'id' => $permissao->id,
                    'nome' => ucwords($permissao->nome)
                ];
            });

        $data = ['roles' => $roles, 'permissaos' => $permissaos];

        return $this->sendResponse($data, 'Recursos recuperados com sucesso');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUpdateUtilizadorUnidadeSanitariaFormRequest $request)
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $input = $request->only(['nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id']);

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

            $utilizador_unidade_sanitaria = $this->utilizador_unidade_sanitaria->create($input);
            $utilizador_unidade_sanitaria->load(['user:id,role_id', 'user.permissaos:codigo,nome', 'user.role:id,codigo,role']);

            $data = [
                'id' => $utilizador_unidade_sanitaria->id,
                'nome' => $utilizador_unidade_sanitaria->nome,
                'email' => $utilizador_unidade_sanitaria->email,
                'contacto' => $utilizador_unidade_sanitaria->contacto,
                'activo' => $utilizador_unidade_sanitaria->activo,
                'nacionalidade' => $utilizador_unidade_sanitaria->nacionalidade,
                'observacoes' => $utilizador_unidade_sanitaria->observacoes,
                'role_codigo' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->id : '',
                'role_id' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->codigo : '',
                'role_nome' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->role : '',
                'permissaos' => !empty($utilizador_unidade_sanitaria->user->permissaos) ? $utilizador_unidade_sanitaria->user->permissaos : '',
            ];

            DB::commit();
            return $this->sendResponse($data, 'Utilizador Unidade Sanitária registado com sucesso');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request('unidade_sanitaria_id');

        $utilizador_unidade_sanitaria = $this->utilizador_unidade_sanitaria
            ->byUnidadeSanitaria($unidade_sanitaria_id)
            ->with('user:id,role_id', 'user.permissaos:id,nome', 'user.role:id,codigo,role')
            ->find($id);

        if (empty($utilizador_unidade_sanitaria)) {
            return $this->sendError('Utilizador Unidade Sanitária não encontrado.');
        }

        $data = [
            'id' => $utilizador_unidade_sanitaria->id,
            'nome' => $utilizador_unidade_sanitaria->nome,
            'email' => $utilizador_unidade_sanitaria->email,
            'email_verificado' => $utilizador_unidade_sanitaria->email_verificado,
            'contacto' => $utilizador_unidade_sanitaria->contacto,
            'activo' => $utilizador_unidade_sanitaria->activo,
            'nacionalidade' => $utilizador_unidade_sanitaria->nacionalidade,
            'observacoes' => $utilizador_unidade_sanitaria->observacoes,
            'role_id' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->id : '',
            'role_codigo' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->codigo : '',
            'role_nome' => !empty($utilizador_unidade_sanitaria->user->role) ? $utilizador_unidade_sanitaria->user->role->role : '',
            'permissaos' => !empty($utilizador_unidade_sanitaria->user->permissaos) ? $utilizador_unidade_sanitaria->user->permissaos : '',
        ];

        return $this->sendResponse($data, 'Utilizador Unidade Sanitária recuperada com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CreateUpdateUtilizadorUnidadeSanitariaFormRequest $request, $id)
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $input = $request->only(['nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id']);

        $utilizador_unidade_sanitaria = $this->utilizador_unidade_sanitaria->byUnidadeSanitaria($input['unidade_sanitaria_id'])->find($id);
        if (empty($utilizador_unidade_sanitaria))
            return $this->sendError('Utilizador Unidade Sanitária não encontrado.');

        $user = $this->user->find($utilizador_unidade_sanitaria->user_id);
        if (empty($user))
            return $this->sendError('Usuário do Utilizador Unidade Sanitária não encontrado.');


        DB::beginTransaction();
        try {

            $utilizador_unidade_sanitaria->update($input);
            $user->update(['nome' => $utilizador_unidade_sanitaria->nome, 'active' => $utilizador_unidade_sanitaria->activo, 'role_id' => $utilizador_unidade_sanitaria->role_id]);
            $user->permissaos()->detach();
            $user->permissaos()->attach($request->permissaos);

            $user->load(['permissaos:codigo,nome', 'role:id,codigo,role']);

            $data = [
                'id' => $utilizador_unidade_sanitaria->id,
                'nome' => $utilizador_unidade_sanitaria->nome,
                'email' => $utilizador_unidade_sanitaria->email,
                'contacto' => $utilizador_unidade_sanitaria->contacto,
                'activo' => $user->active ? 1 : 0,
                'nacionalidade' => $utilizador_unidade_sanitaria->nacionalidade,
                'observacoes' => $utilizador_unidade_sanitaria->observacoes,
                'role_id' => !empty($user->role) ? $user->role->id : '',
                'role_codigo' => !empty($user->role) ? $user->role->codigo : '',
                'role_nome' => !empty($user->role) ? $user->role->role : '',
                'permissaos' => !empty($user->permissaos) ? $user->permissaos : '',
            ];

            DB::commit();
            return $this->sendResponse($data, 'Utilizador Unidade Sanitária actualizado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request('unidade_sanitaria_id');

        $utilizador_unidade_sanitaria = $this->utilizador_unidade_sanitaria->byUnidadeSanitaria($unidade_sanitaria_id)->find($id);
        if (empty($utilizador_unidade_sanitaria))
            return $this->sendError('Utilizador Unidade Sanitária não encontrado.');

        $user = $this->user->find($utilizador_unidade_sanitaria->user_id);
        if (empty($user))
            return $this->sendError('Usuário do Utilizador Unidade Sanitária não encontrado.');

        DB::beginTransaction();
        try {
            $utilizador_unidade_sanitaria->delete();
            $user->delete();
            DB::commit();
            return $this->sendSuccess('Utilizador Unidade Sanitária removido com sucesso');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
