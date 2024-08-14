<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UnidadeSanitaria;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\UtilizadorUnidadeSanitaria;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateUtilizadorUnidadeSanitariaFormRequest;
use App\Models\Permissao;
use Symfony\Component\Console\Input\Input;

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
        $this->middleware(["CheckRole:1:6"]);
    }

    /**
     * Display a listing of the UtilizadorUnidadeSanitaria.
     * GET|HEAD /utilizador_unidade_sanitaria
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $authenticated = Auth::user();

        /* if ($authenticated->role->codigo == 1) {
            $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria->byGestorUnidadeSanitaria()->get();
        } else {
            $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria->all();
        } */


        /* if ($authenticated->role->codigo == 1) {
            $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria
            ->with('user:id,active', 'role:id,role', 'unidadeSanitaria:id,nome')
            ->byGestorUnidadeSanitaria()
            ->get(['id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id', 'user_id'])
            ->map( function ($utilizador_unidade_sanitaria) {
                $utilizador_unidade_sanitaria->activo = $utilizador_unidade_sanitaria->user->active;
                return $utilizador_unidade_sanitaria->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidadeSanitaria', 'role_id', 'role');
            });
        } else {
            $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria
            ->with('user:id,active', 'role:id,role', 'unidadeSanitaria:id,nome')
            ->get(['id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id', 'user_id'])
            ->map( function ($utilizador_unidade_sanitaria) {
                $utilizador_unidade_sanitaria->activo = $utilizador_unidade_sanitaria->user->active;
                return $utilizador_unidade_sanitaria->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidadeSanitaria', 'role_id', 'role');
            });
        } */

        $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria
            ->with('user:id,active', 'user.permissaos:id,nome', 'role:id,role', 'unidadeSanitaria:id,nome')
            ->get(['id', 'nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id', 'user_id'])
            ->map( function ($utilizador_unidade_sanitaria) {
                // $utilizador_unidade_sanitaria->activo = $utilizador_unidade_sanitaria->user->active;
                // return $utilizador_unidade_sanitaria->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidadeSanitaria', 'role_id', 'role');

                return [
                    'id' => $utilizador_unidade_sanitaria->id,
                    'nome' => $utilizador_unidade_sanitaria->nome,
                    'email' => $utilizador_unidade_sanitaria->email,
                    'email_verificado' => $utilizador_unidade_sanitaria->email_verificado,
                    'contacto' => $utilizador_unidade_sanitaria->contacto,
                    'activo' => $utilizador_unidade_sanitaria->activo,
                    'nacionalidade' => $utilizador_unidade_sanitaria->nacionalidade,
                    'observacoes' => $utilizador_unidade_sanitaria->observacoes,
                    'role_id' => $utilizador_unidade_sanitaria->role_id,
                    'unidadeSanitaria' => !empty($utilizador_unidade_sanitaria->unidadeSanitaria) ? $utilizador_unidade_sanitaria->unidadeSanitaria : null,
                    'role' => !empty($utilizador_unidade_sanitaria->role) ? $utilizador_unidade_sanitaria->role : null,
                    'permissaos' => !empty($utilizador_unidade_sanitaria->user->permissaos) ? $utilizador_unidade_sanitaria->user->permissaos : null,
                ];
            });

        return $this->sendResponse($utilizadores_unidade_sanitaria->toArray(), 'Utilizador Unidade Sanitária retrieved successfully');
    }

     /**
     * Retrieve a listing of resources used to create the UtilizadorUnidadeSanitaria.
     * GET|HEAD /utilizador_unidade_sanitaria/create
     *
     * @return Response
     */
    public function create()
    {
        $roles = Role::GestorUnidadeSanitaria()->get();
        $permissaos = $this->permissao
        ->bySeccaoUnidadeSanitaria()
        ->get(['id', 'nome'])
        ->map( function ($permissao) {
            return [
                'id' => $permissao->id,
                'nome' => ucwords($permissao->nome)
            ];
        });

        $data = ['roles' => $roles, 'permissaos' => $permissaos];

        return $this->sendResponse($data, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created UtilizadorUnidadeSanitaria in storage.
     * POST /utilizador_unidade_sanitaria
     *
     * @param CreateUpdateUtilizadorUnidadeSanitariaFormRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateUtilizadorUnidadeSanitariaFormRequest $request)
    {
        // dd($request->all());

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
            
            /** @var UtilizadorUnidadeSanitaria $utilizador_unidade_sanitaria */
            $utilizador_unidade_sanitaria = $this->utilizador_unidade_sanitaria->create($input);

            DB::commit();
            return $this->sendResponse($utilizador_unidade_sanitaria->toArray(), 'Utilizador Unidade Sanitária registado com sucesso');

        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified UtilizadorUnidadeSanitaria.
     * GET|HEAD /utilizador_unidade_sanitaria/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var UtilizadorUnidadeSanitaria $utilizador_unidade_sanitaria */
        $utilizador_unidade_sanitaria = $this->utilizador_unidade_sanitaria->find($id);

        if (empty($utilizador_unidade_sanitaria)) {
            return $this->sendError('Utilizador Unidade Sanitária não encontrado.');
        }

        return $this->sendResponse($utilizador_unidade_sanitaria->toArray(), 'Utilizador Unidade Sanitária retornado com sucesso.');
    }

    /**
     * Update the specified UtilizadorUnidadeSanitaria in storage.
     * PUT/PATCH /utilizador_unidade_sanitaria/{id}
     *
     * @param int $id
     * @param CreateUpdateUtilizadorUnidadeSanitariaFormRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateUtilizadorUnidadeSanitariaFormRequest $request)
    {
        $input = $request->only(['nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id']);
        // dd($input);
        $utilizador_unidade_sanitaria = $this->utilizador_unidade_sanitaria->find($id);

        if (empty($utilizador_unidade_sanitaria)) {
            return $this->sendError('Utilizador Unidade Sanitária não encontrado.');
        }

        DB::beginTransaction();
        try {
            $utilizador_unidade_sanitaria->update($input);

            $user = $this->user->find($utilizador_unidade_sanitaria->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('Usuário do Utilizador Unidade Sanitária não encontrado.');
            }

            $user->update(['nome' => $utilizador_unidade_sanitaria->nome, 'active' => $utilizador_unidade_sanitaria->activo, 'role_id' => $utilizador_unidade_sanitaria->role_id]);
            $user->permissaos()->detach();
            $user->permissaos()->attach($request->permissaos);

            DB::commit();
            return $this->sendResponse($utilizador_unidade_sanitaria->toArray(), 'Utilizador Unidade Sanitária actualizado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified UtilizadorUnidadeSanitaria from storage.
     * DELETE /utilizador_unidade_sanitaria/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var UtilizadorUnidadeSanitaria $utilizador_unidade_sanitaria */
        $utilizador_unidade_sanitaria = $this->utilizador_unidade_sanitaria->find($id);

        if (empty($utilizador_unidade_sanitaria)) {
            return $this->sendError('Utilizador Unidade Sanitária not found');
        }

        DB::beginTransaction();
        try {

            /** @var User $user belongs to UtilizadorUnidadeSanitaria being updated*/
            $user = $this->user->find($utilizador_unidade_sanitaria->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('User of Utilizador Unidade Sanitária not found');
            }

            $utilizador_unidade_sanitaria->delete();
            $user->delete();
            DB::commit();
            return $this->sendSuccess('Utilizador Unidade Sanitária deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

}
