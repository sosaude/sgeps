<?php

namespace App\Http\Controllers\API\Farmacia;

use App\Models\Role;
use App\Models\User;
use App\Models\Farmacia;
use App\Models\Permissao;
use Illuminate\Http\Request;
use App\Models\UtilizadorFarmacia;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateUpdateUtilizadorFarmaciaFormRequest;

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
        // $this->middleware(["CheckRole:1:2"]);
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

        $farmacia_id = request('farmacia_id');
        $utilizadores_farmacia = $this->utilizador_farmacia
            ->byFarmacia($farmacia_id)
            ->with(['user:id,role_id', 'user.permissaos:id,nome', 'user.role:id,codigo,role'])
            ->get()
            ->map(function ($utilizador_farmacia) {

                return [
                    'id' => $utilizador_farmacia->id,
                    'nome' => $utilizador_farmacia->nome,
                    'email' => $utilizador_farmacia->email,
                    'email_verificado' => $utilizador_farmacia->email_verificado,
                    'contacto' => $utilizador_farmacia->contacto,
                    'activo' => $utilizador_farmacia->activo,
                    'nacionalidade' => $utilizador_farmacia->nacionalidade,
                    'observacoes' => $utilizador_farmacia->observacoes,
                    'role_id' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->id : '',
                    'role_codigo' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->codigo : '',
                    'role_nome' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->role : '',
                    'permissaos' => !empty($utilizador_farmacia->user->permissaos) ? $utilizador_farmacia->user->permissaos : '',
                ];
            });

        return $this->sendResponse($utilizadores_farmacia->toArray(), 'Utilizadores da Farmácia recuperados com sucesso');
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

        $roles = Role::select('id', 'role')->whereHas('seccao', function ($q) {
            $q->where('seccao_id', 3);
        })->get();
        $permissaos = $this->permissao
            ->bySeccaoFarmacia()
            ->get(['id', 'nome'])
            ->map(function ($permissao) {
                return [
                    'id' => $permissao->id,
                    'nome' => ucwords($permissao->nome)
                ];
            });

        $data = ['roles' => $roles, 'permissaos' => $permissaos];

        return $this->sendResponse($data, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateUpdateUtilizadorFarmaciaFormRequest $request)
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $input = $request->only(['nome', 'email', 'contacto', 'numero_caderneta', 'activo', 'categoria_profissional', 'nacionalidade', 'observacoes', 'farmacia_id', 'role_id']);
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

            $utilizador_farmacia = $this->utilizador_farmacia->create($input);
            $utilizador_farmacia->load(['user:id,role_id', 'user.permissaos:codigo,nome', 'user.role:id,codigo,role']);
            // dd($user);

            $data = [
                'id' => $utilizador_farmacia->id,
                'nome' => $utilizador_farmacia->nome,
                'email' => $utilizador_farmacia->email,
                'contacto' => $utilizador_farmacia->contacto,
                'activo' => $user->active ? 1 : 0,
                'nacionalidade' => $utilizador_farmacia->nacionalidade,
                'observacoes' => $utilizador_farmacia->observacoes,
                'role_id' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->id : '',
                'role_codigo' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->codigo : '',
                'role_nome' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->role : '',
                'permissaos' => !empty($utilizador_farmacia->user->permissaos) ? $utilizador_farmacia->user->permissaos : '',
            ];

            DB::commit();
            return $this->sendResponse($data, 'Utilizador Farmáscia registado com sucesso!');
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

        $farmacia_id = request('farmacia_id');
        /** @var UtilizadorFarmacia $utilizadorFarmacia */
        $utilizador_farmacia = $this->utilizador_farmacia
            ->byFarmacia($farmacia_id)
            ->with(['user:id,role_id', 'user.permissaos:id,nome', 'user.role:id,codigo,role'])
            ->find($id);

        if (empty($utilizador_farmacia)) {
            return $this->sendError('Utilizador Farmacia não encontrado');
        }

        $utilizador_farmacia->load(['role:id,codigo,role']);
        $data = [
            'id' => $utilizador_farmacia->id,
            'nome' => $utilizador_farmacia->nome,
            'email' => $utilizador_farmacia->email,
            'email_verificado' => $utilizador_farmacia->email_verificado,
            'contacto' => $utilizador_farmacia->contacto,
            'activo' => $utilizador_farmacia->activo,
            'nacionalidade' => $utilizador_farmacia->nacionalidade,
            'observacoes' => $utilizador_farmacia->observacoes,
            'role_id' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->id : '',
            'role_codigo' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->codigo : '',
            'role_nome' => !empty($utilizador_farmacia->user->role) ? $utilizador_farmacia->user->role->role : '',
            'permissaos' => !empty($utilizador_farmacia->user->permissaos) ? $utilizador_farmacia->user->permissaos : '',
        ];

        return $this->sendResponse($data, 'Utilizador Farmacia recuperado com sucesso.');
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
    public function update(CreateUpdateUtilizadorFarmaciaFormRequest $request, $id)
    {
        if (Gate::denies('gerir utilizador')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $input = $request->only(['nome', 'email', 'contacto', 'numero_caderneta', 'activo', 'categoria_profissional', 'nacionalidade', 'observacoes', 'farmacia_id', 'role_id']);

        $utilizador_farmacia = $this->utilizador_farmacia->byFarmacia($request->farmacia_id)->find($id);
        if (empty($utilizador_farmacia))
            return $this->sendError('Utilizador Farmacia não encontrado');

        $user = $this->user->find($utilizador_farmacia->user_id);
        if (empty($user))
            return $this->sendError('Usuário do Utilizador Farmácia não encontrado');

        DB::beginTransaction();
        try {

            $utilizador_farmacia->update($input);
            $user->update(['nome' => $utilizador_farmacia->nome, 'active' => $utilizador_farmacia->activo, 'role_id' => $utilizador_farmacia->role_id]);
            $user->permissaos()->detach();
            $user->permissaos()->attach($request->permissaos);

            $user->load(['permissaos:codigo,nome', 'role:id,codigo,role']);

            $data = [
                'id' => $utilizador_farmacia->id,
                'nome' => $utilizador_farmacia->nome,
                'email' => $utilizador_farmacia->email,
                'contacto' => $utilizador_farmacia->contacto,
                'activo' => $user->active ? 1 : 0,
                'nacionalidade' => $utilizador_farmacia->nacionalidade,
                'observacoes' => $utilizador_farmacia->observacoes,
                'role_id' => !empty($user->role) ? $user->role->id : '',
                'role_codigo' => !empty($user->role) ? $user->role->codigo : '',
                'role_nome' => !empty($user->role) ? $user->role->role : '',
                'permissaos' => !empty($user->permissaos) ? $user->permissaos : '',
            ];

            DB::commit();
            return $this->sendResponse($data, 'Utilizador Farmacia actualizado com sucesso!');
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

        $farmacia_id = request('farmacia_id');

        $utilizador_farmacia = $this->utilizador_farmacia->byFarmacia($farmacia_id)->find($id);
        if (empty($utilizador_farmacia))
            return $this->sendError('Utilizador Farmacia não encontrado');

        $user = $this->user->find($utilizador_farmacia->user_id);
        if (empty($user))
            return $this->sendError('Usuário do Utilizador Farmacia não encontrado');

        DB::beginTransaction();
        try {

            $utilizador_farmacia->delete();
            $user->delete();
            DB::commit();
            return $this->sendSuccess('Utilizador Farmacia removido com sucesso');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
