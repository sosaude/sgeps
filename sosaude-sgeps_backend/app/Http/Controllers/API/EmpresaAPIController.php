<?php

namespace App\Http\Controllers\API;

use Response;
use App\Models\Tenant;
use App\Models\Empresa;
use App\Models\Farmacia;
use Illuminate\Http\Request;
use App\Models\CategoriaEmpresa;
use App\Models\UnidadeSanitaria;
use App\Models\UtilizadorEmpresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNewOrganizationMail;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateEmpresaAPIRequest;
use App\Http\Requests\API\UpdateEmpresaAPIRequest;
use App\Http\Requests\API\CreateUpdateEmpresaFormRequest;

/**
 * Class EmpresaController
 * @package App\Http\Controllers\API
 */

class EmpresaAPIController extends AppBaseController
{
    private $empresa;
    private $categoria_empresa;
    private $utilizador_empresa;
    /**
     * Create a new EmpresaAPIController instance.
     *
     * @return void
     */
    public function __construct(Empresa $empresa, CategoriaEmpresa $categoria_empresa, UtilizadorEmpresa $utilizador_empresa)
    {
        $this->middleware(["CheckRole:1"]);
        $this->empresa = $empresa;
        $this->categoria_empresa = $categoria_empresa;
        $this->utilizador_empresa = $utilizador_empresa;
    }

    /**
     * Display a listing of the Empresa.
     * GET|HEAD /empresas
     *
     * @param Request $request
     * @return Response
     */
    public function index()
    {
        /* $farmacias_emails = Farmacia::where('email', '!=', null)
            ->get()
            ->filter(function ($farmacia, $key) {
                return filter_var($farmacia->email, FILTER_VALIDATE_EMAIL);
            })->pluck('email')->toArray();

        $unidades_sanitarias_emails = UnidadeSanitaria::where('email', '!=', null)
            ->get()
            ->filter(function ($unidade_sanitaria, $key) {
                return filter_var($unidade_sanitaria->email, FILTER_VALIDATE_EMAIL);
            })->pluck('email')->toArray();

        $emails = array_merge($farmacias_emails, $unidades_sanitarias_emails);
        // dd($emails);
        $when = now()->addSeconds(10);

        return (new SendNewOrganizationMail('Empresa', 'Nome da Empresa'))->render();

        foreach ($emails as $email) {
            Mail::to($email)->later($when, new SendNewOrganizationMail('Empresa', 'Nome da Empresa'));
        } */

        



        $empresas = $this->empresa->orderBy('nome', 'asc')->get(['id', 'nome', 'endereco', 'email', 'nuit', 'contactos', 'delegacao', 'latitude', 'longitude', 'categoria_empresa_id']);

        return $this->sendResponse($empresas->toArray(), 'Empresas retrieved successfully');
    }

    /**
     * Retrieve a listing of resources (CategoriaEmpresa) used to create the Empresa.
     * GET|HEAD /empresas/create
     *
     * @return Response
     */
    public function create()
    {
        $categorias_empresa = $this->categoria_empresa->select('id', 'nome')->get();

        return $this->sendResponse($categorias_empresa, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created Empresa in storage.
     * POST /empresas
     *
     * @param CreateEmpresaAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateEmpresaFormRequest $request)
    {

        /* DB::beginTransaction();
        try {
        $empresa = Empresa::create($input);
        DB::commit();
        return $this->sendResponse($empresa->toArray(), 'Empresa saved successfully');
        } catch (Exception $e) {
        DB::rollback();
        return $this->sendError($e->getMessage());
        } */

        DB::beginTransaction();
        try {

            $tenant = new Tenant();
            $tenant->nome = $request->nome;
            $tenant->save();

            $input = [
                "nome" => $request->nome,
                "categoria_empresa_id" => $request->categoria_empresa_id,
                "endereco" => $request->endereco,
                "email" => $request->email,
                "nuit" => $request->nuit,
                "contactos" => $request->contactos,
                "delegacao" => $request->delegacao,
                "tenant_id" => $tenant->id,
            ];
            // dd($input);
            $empresa = Empresa::create($input);
            DB::commit();
            return $this->sendResponse($empresa->toArray(), 'Empresa saved successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified Empresa.
     * GET|HEAD /empresas/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Empresa $empresa */
        $empresa = Empresa::find($id);

        if (empty($empresa)) {
            return $this->sendError('Empresa not found');
        }

        return $this->sendResponse($empresa->toArray(), 'Empresa retrieved successfully');
    }

    /**
     * Update the specified Empresa in storage.
     * PUT/PATCH /empresas/{id}
     *
     * @param int $id
     * @param UpdateEmpresaAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateEmpresaFormRequest $request)
    {
        /** @var array UpdateEmpresaAPIRequest $input */
        $input = [
            "nome" => $request->nome,
            "categoria_empresa_id" => $request->categoria_empresa_id,
            "endereco" => $request->endereco,
            "email" => $request->email,
            "nuit" => $request->nuit,
            "contactos" => $request->contactos,
            "delegacao" => $request->delegacao,
        ];

        /** @var Empresa $empresa */
        $empresa = Empresa::find($id);

        if (empty($empresa)) {
            return $this->sendError('Empresa not found');
        }

        DB::beginTransaction();
        try {
            $empresa->fill($input);
            $empresa->save();

            $tenant = Tenant::find($empresa->tenant_id);
            $tenant->nome = $empresa->nome;
            $tenant->save();

            DB::commit();
            return $this->sendResponse($empresa->toArray(), 'Empresa updated successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified Empresa from storage.
     * DELETE /empresas/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Empresa $empresa */
        $empresa = $this->empresa->with('utilizadoresEmpresas', 'beneficiarios.dependentes')->find($id);

        if (empty($empresa)) {
            return $this->sendError('Empresa not found');
        }

        DB::beginTransaction();
        try {

            /* foreach ($empresa->utilizadoresEmpresas as $utilizador_empresa) {
                $utilizador_empresa->delete();
            }
            foreach ($empresa->beneficiarios as $beneficiario) {
                foreach ($beneficiario->dependentes as $dependente) {
                    // dd($dependente);
                    $dependente->delete();
                }
                $beneficiario->delete();
            } */

            $tenant = $empresa->tenant;
            
            $empresa->delete();

            if(!empty($tenant)) {
                $tenant->delete();
            }
            
            DB::commit();
            return $this->sendSuccess('Empresa deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        } catch (QueryException $e) {
            DB::rollback();
            return $this->sendError('Erro de integridade referêncial na Base de Dados! Contacte o Administrador.', 500);
        }
    }

    /**
     * Retrieve the utilizador_farmacia oh the Farmacia from storage.
     * GET /empresas/utilizadores/{id}
     *
     * @param int $id
     *
     *
     * @return Response
     */
    public function utilizadores($empresa_id)
    {
        /* $utilizadores_empresa = $this->utilizador_empresa
        ->with('user:id,active', 'role:id,role', 'empresa:id,nome')
        ->byEmpresa($empresa_id)
        ->byGestorEmpresa()            
        ->get(['id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id', 'user_id'])
        ->map( function ($utilizador_empresa) {
            $utilizador_empresa->activo = $utilizador_empresa->user->active;
            return $utilizador_empresa->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'role_id', 'empresa_id', 'role', 'empresa');
        }); */

        $utilizadores_empresa = $this->utilizador_empresa
            ->byEmpresa($empresa_id)
            ->byGestorEmpresa()
            ->with('user:id,active', 'role:id,role', 'user.permissaos:id,nome', 'empresa:id,nome')
            ->get(['id', 'nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id', 'user_id'])
            ->map(function ($utilizador_empresa) {
                // $utilizador_empresa->activo = $utilizador_empresa->user->active;
                // return $utilizador_empresa->only('id', 'nome', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'empresa_id', 'role_id');
                $utilizador_empresa->permissaos = $utilizador_empresa->user->permissaos;
                return $utilizador_empresa->only('id', 'nome', 'email', 'contacto', 'activo', 'nacionalidade', 'observacoes', 'role_id', 'role', 'empresa_id', 'empresa', 'permissaos');
            });

        return $this->sendResponse($utilizadores_empresa->toArray(), 'Utilizadores Empresa retrieved successfully');
    }
}
