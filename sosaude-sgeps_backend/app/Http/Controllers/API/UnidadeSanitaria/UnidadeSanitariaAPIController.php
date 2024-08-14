<?php

namespace App\Http\Controllers\API\UnidadeSanitaria;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\UnidadeSanitaria;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\CategoriaUnidadeSanitaria;
use App\Models\UtilizadorUnidadeSanitaria;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateUpdateUnidadeSanitariaFormRequest;

class UnidadeSanitariaAPIController extends AppBaseController
{
    private $categoria_unidade_sanitaria;
    private $unidade_sanitaria;
    private $utilizador_unidade_sanitaria;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(CategoriaUnidadeSanitaria $categoria_unidade_sanitaria, UnidadeSanitaria $unidade_sanitaria, UtilizadorUnidadeSanitaria $utilizador_unidade_sanitaria)
    {
        // $this->middleware(["CheckRole:1"]);
        $this->categoria_unidade_sanitaria = $categoria_unidade_sanitaria;
        $this->unidade_sanitaria = $unidade_sanitaria;
        $this->utilizador_unidade_sanitaria = $utilizador_unidade_sanitaria;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        if (Gate::denies('gerir perfil')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        $categorias_unidade_sanitaria = $this->categoria_unidade_sanitaria->get(['id', 'codigo', 'nome']);
        $unidade_sanitaria = $this->unidade_sanitaria->find($unidade_sanitaria_id);

        if (empty($unidade_sanitaria)) {
            return $this->sendError('Unidade Sanitária não encontrada', 404);
        }

        $data = [
            'categorias_unidade_sanitaria' => $categorias_unidade_sanitaria,
            'unidade_sanitaria' => $unidade_sanitaria,
        ];

        return $this->sendResponse($data, '');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CreateUpdateUnidadeSanitariaFormRequest $request, $id)
    {
        if (Gate::denies('gerir perfil')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        // $request->validate(['latitude' => 'required|numeric', 'longitude' => 'required|numeric']);

        $unidade_sanitaria_id = request('unidade_sanitaria_id');
        $input = [
            "nome" => $request->nome,
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
            return $this->sendError('Unidade Sanitária não encontrada', 404);
        }

        if ($unidade_sanitaria->id !== $unidade_sanitaria_id) {
            return $this->sendError('A Unidade Sanitária não corresponde à Unidade Sanitária do utilizador', 404);
        }
        // dd($unidade_sanitaria);
        DB::beginTransaction();
        try {
            $unidade_sanitaria->fill($input);
            $unidade_sanitaria->save();

            $tenant = Tenant::find($unidade_sanitaria->tenant_id);
            $tenant->nome = $unidade_sanitaria->nome;
            $tenant->save();
            DB::commit();
            return $this->sendResponse($unidade_sanitaria->toArray(), 'Unidade Sanitária actualizada com sucesso!');
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
        //
    }
}
