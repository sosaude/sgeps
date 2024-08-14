<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateUnidadeSanitariaFormRequest;
use App\Models\CategoriaUnidadeSanitaria;
use App\Models\Tenant;
use App\Models\UnidadeSanitaria;
use App\Models\UtilizadorUnidadeSanitaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnidadeSanitariaAPIController extends AppBaseController
{
    private $unidade_sanitaria;
    private $utilizador_unidade_sanitaria;
    private $categoria_unidade_sanitaria;
    /**
     * Create a new UnidadeSanitariaContorller instance.
     *
     * @return void
     */
    public function __construct(UnidadeSanitaria $unidade_sanitaria, UtilizadorUnidadeSanitaria $utilizador_unidade_sanitaria, CategoriaUnidadeSanitaria $categoria_unidade_sanitaria)
    {
        $this->middleware(["CheckRole:1"]);
        $this->unidade_sanitaria = $unidade_sanitaria;
        $this->utilizador_unidade_sanitaria = $utilizador_unidade_sanitaria;
        $this->categoria_unidade_sanitaria = $categoria_unidade_sanitaria;
    }

    /**
     * Display a listing of the UnidadeSanitaria.
     * GET|HEAD /unidades_sanitarias
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $unidade_sanitaria = $this->unidade_sanitaria->with('categoriaUnidadeSanitaria:id,nome')->orderBy('nome', 'asc')->get([
            'id',
            'categoria_unidade_sanitaria_id',
            'nome',
            'endereco',
            'email',
            'contactos',
            'nuit',
            'latitude',
            'longitude',
        ]);

        return $this->sendResponse($unidade_sanitaria->toArray(), 'Unidades Sanitárias retrieved successfully');
    }

    /**
     * Retrieve a listing of resources used to create the UtilizadorEmpresa.
     * GET|HEAD /utilizador_empresa/create
     *
     * @return Response
     */
    public function create()
    {
        $categorias_unidade_sanitaria = $this->categoria_unidade_sanitaria->get(['id', 'nome']);

        if(!$categorias_unidade_sanitaria) {
            return $this->sendError('Categorias Unidade Sanitária Not Found1', 404);
        }

        return $this->sendResponse($categorias_unidade_sanitaria, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created UnidadeSanitaria in storage.
     * POST /unidades_sanitarias
     *
     * @param CreateUpdateUnidadeSanitariaFormRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateUnidadeSanitariaFormRequest $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $tenant = new Tenant();
            $tenant->nome = $request->nome;
            $tenant->save();

            $input = [
                "categoria_unidade_sanitaria_id" => $request->categoria_unidade_sanitaria_id,
                "nome" => $request->nome,
                "email" => $request->email,
                "endereco" => $request->endereco,
                "email" => $request->email,
                "nuit" => $request->nuit,
                "contactos" => $request->contactos,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'tenant_id' => $tenant->id,
            ];

            /** @var UnidadeSanitaria $unidade_sanitaria */
            $unidade_sanitaria = $this->unidade_sanitaria->create($input);
            DB::commit();
            return $this->sendResponse($unidade_sanitaria->toArray(), 'Unidade Sanitária saved successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified UnidadeSanitaria.
     * GET|HEAD /unidades_sanitarias/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var UnidadeSanitaria $unidade_sanitaria */
        $unidade_sanitaria = $this->unidade_sanitaria->with('categoriaUnidadeSanitaria:id,nome')->find($id);

        if (empty($unidade_sanitaria)) {
            return $this->sendError('Unidade Sanitária not found');
        }

        return $this->sendResponse($unidade_sanitaria->toArray(), 'Unidade Sanitária retrieved successfully');
    }

    /**
     * Update the specified UnidadeSanitaria in storage.
     * PUT/PATCH /unidades_sanitarias/{id}
     *
     * @param int $id
     * @param CreateUpdateUnidadeSanitariaFormRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateUnidadeSanitariaFormRequest $request)
    {

        $input = [
            "nome" => $request->nome,
            "email" => $request->email,
            "endereco" => $request->endereco,
            "email" => $request->email,
            "nuit" => $request->nuit,
            "contactos" => $request->contactos,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        /** @var UnidadeSanitaria $unidade_sanitaria */
        $unidade_sanitaria = $this->unidade_sanitaria->find($id);

        if (empty($unidade_sanitaria)) {
            return $this->sendError('Unidade Sanitária not found');
        }

        DB::beginTransaction();
        try {
            $unidade_sanitaria->fill($input);
            $unidade_sanitaria->save();

            $tenant = Tenant::find($unidade_sanitaria->tenant_id);
            $tenant->nome = $unidade_sanitaria->nome;
            $tenant->save();
            DB::commit();
            return $this->sendResponse($unidade_sanitaria->toArray(), 'Unidade Sanitária updated successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified UnidadeSanitaria from storage.
     * DELETE /unidades_sanitarias/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var UnidadeSanitaria $unidade_sanitaria */
        $unidade_sanitaria = $this->unidade_sanitaria->with('utilizadoresUnidadeSanitaria')->find($id);

        if (empty($unidade_sanitaria)) {
            return $this->sendError('Unidade Sanitária not found');
        }

        DB::beginTransaction();
        try {

            /* foreach ($unidade_sanitaria->utilizadoresUnidadeSanitaria as $utilizador_unidade_sanitaria) {
                $utilizador_unidade_sanitaria->delete();
            } */

            $unidade_sanitaria->delete();
            DB::commit();
            return $this->sendSuccess('Unidade Sanitária deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        } catch (QueryException $e) {
            DB::rollback();
            return $this->sendError('Erro de integridade referêncial na Base de Dados! Contacte o Administrador.', 500);
        }
    }

    /**
     * Retrieve the utilizador_unidade_sanitaria oh the UnidadeSanitaria from storage.
     * GET /unidades_sanitarias/utilizadores/{id}
     *
     * @param int $id
     *
     *
     * @return Response
     */
    public function utilizadores($unidade_sanitaria_id)
    {
        /* $authenticated = Auth::user();

        if ($authenticated->role->codigo == 1) {
            $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria->with('user:id,active', 'role:id,role', 'unidadeSanitaria:id,nome')->where('unidade_sanitaria_id', $unidade_sanitaria_id)->byGestorUnidadeSanitaria()
            ->get(['id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id', 'user_id'])
            ->map( function ($utilizador_unidade_sanitaria) {
                $utilizador_unidade_sanitaria->activo = $utilizador_unidade_sanitaria->user->active;
                return $utilizador_unidade_sanitaria->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidadeSanitaria', 'role_id', 'role');
            });
        } else {
            $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria->with('user:id,active', 'role:id,role', 'unidadeSanitaria:id,nome')->where('unidade_sanitaria_id', $unidade_sanitaria_id)
            ->get(['id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id', 'user_id'])
            ->map( function ($utilizador_unidade_sanitaria) {
                $utilizador_unidade_sanitaria->activo = $utilizador_unidade_sanitaria->user->active;
                return $utilizador_unidade_sanitaria->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidadeSanitaria', 'role_id', 'role');
            });
        } */

        $utilizadores_unidade_sanitaria = $this->utilizador_unidade_sanitaria
        ->with('user:id,active', 'role:id,role', 'unidadeSanitaria:id,nome', 'user.permissaos:id,nome')
        ->where('unidade_sanitaria_id', $unidade_sanitaria_id)
            ->get(['id', 'nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidade_sanitaria_id', 'role_id', 'user_id'])
            ->map( function ($utilizador_unidade_sanitaria) {
                $utilizador_unidade_sanitaria->permissaos = $utilizador_unidade_sanitaria->user->permissaos;
                return $utilizador_unidade_sanitaria->only('id', 'nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'unidadeSanitaria', 'role_id', 'role', 'permissaos');
            });

        return $this->sendResponse($utilizadores_unidade_sanitaria->toArray(), 'Utilizadores Unidade Sanitária retrieved successfully');
    }

}
