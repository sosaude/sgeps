<?php

namespace App\Http\Controllers\API\Farmacia;

use App\Models\Tenant;
use App\Models\Farmacia;
use Illuminate\Http\Request;
use App\Models\UtilizadorFarmacia;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Tenant\Farmacia\CreateUpdateFarmaciaFormRequest;

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
        // $this->middleware(["CheckRole:1"]);
        $this->farmacia = $farmacia;
        $this->utilizador_farmacia = $utilizador_farmacia;
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
        //
        
        $farmacia_id = request('farmacia_id');
        $farmacia = $this->farmacia->find($farmacia_id);

        if (empty($farmacia)) {
            return $this->sendError('Farmácia não encontrada', 404);
        }
        return $this->sendResponse($farmacia->toArray(), '');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CreateUpdateFarmaciaFormRequest $request, $id)
    {
        if (Gate::denies('gerir perfil')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        // dd($request->all());
        $farmacia_id = request('farmacia_id');
        /** @var Farmacia $farmacia */
        $farmacia = $this->farmacia->find($id);
        $input = [
            "nome" => $request->nome,
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

        if (empty($farmacia)) {
            return $this->sendError('Farmacia não encontrada found');
        }

        if ($farmacia->id !== $farmacia_id) {
            return $this->sendError('Farmacia não corresponde à Farmácia do Utilizador');
        }

        DB::beginTransaction();
        try {
            $farmacia->fill($input);
            $farmacia->save();

            $tenant = Tenant::find($farmacia->tenant_id);
            $tenant->nome = $farmacia->nome;
            $tenant->save();

            DB::commit();
            return $this->sendResponse($farmacia->toArray(), 'Farmacia actualizada com sucesso!');
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
