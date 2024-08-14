<?php

namespace App\Http\Controllers\API\Empresa;

use Excel;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Imports\BeneficiarioImport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\DependenteBeneficiario;
use App\Http\Controllers\AppBaseController;
use App\Imports\DependenteBeneficiarioImport;


class DependenteBeneficiarioController extends AppBaseController
{
    /** @var DependenteBeneficiario $dependente_beneficiario */
    private $dependente_beneficiario;

    public function __construct(DependenteBeneficiario $dependente_beneficiario)
    {
        $this->dependente_beneficiario = $dependente_beneficiario;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = $request->empresa_id;
        $dependentes_beneficiarios = $this->dependente_beneficiario
        ->byEmpresa($empresa_id)
        ->with([
            'beneficiario:id,nome,numero_beneficiario',
            'user:id,active,codigo_login'
        ])
        ->get([
            'id','activo','nome','numero_identificacao','email','parantesco','endereco','bairro','telefone','genero','data_nascimento','doenca_cronica','doenca_cronica_nome','beneficiario_id','user_id'
        ])
        ->map( function ($dependente_beneficiario) {
            return [
                'id' => $dependente_beneficiario->id,
                'nome' => $dependente_beneficiario->nome,
                'numero_identificacao' => $dependente_beneficiario->numero_identificacao,
                'email' => $dependente_beneficiario->email,
                'codigo_acesso' => !empty($dependente_beneficiario->user) ? $dependente_beneficiario->user->codigo_login : null,
                'parantesco' => $dependente_beneficiario->parantesco,
                'endereco' => $dependente_beneficiario->endereco,
                'bairro' => $dependente_beneficiario->bairro,
                'telefone' => $dependente_beneficiario->telefone,
                'genero' => $dependente_beneficiario->genero,
                'data_nascimento' => $dependente_beneficiario->data_nascimento,
                'doenca_cronica' => $dependente_beneficiario->doenca_cronica,
                'doenca_cronica_nome' => $dependente_beneficiario->doenca_cronica_nome,
                'utilizador_activo' => $dependente_beneficiario->activo,
                'numero_beneficiario' => !empty($dependente_beneficiario->beneficiario) ? $dependente_beneficiario->beneficiario->numero_beneficiario : null,
                'beneficiario_nome' => !empty($dependente_beneficiario->beneficiario) ? $dependente_beneficiario->beneficiario->nome : "",
            ];
        });

        return $this->sendResponse($dependentes_beneficiarios->toArray(), 'Dependentes Beneficiarios retrieved successfully');
    }
    public function indexBeneficiario(Request $request)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = $request->empresa_id;
        $dependentes_beneficiarios = $this->dependente_beneficiario
        ->byEmpresa($empresa_id)
        ->with([
            'beneficiario:id,nome,numero_beneficiario',
            'user:id,active,codigo_login'
        ])
        ->get([
            'id','activo','nome','numero_identificacao','email','parantesco','endereco','bairro','telefone','genero','data_nascimento','doenca_cronica','doenca_cronica_nome','beneficiario_id','user_id'
        ])
        ->map( function ($dependente_beneficiario) {
            
            if ($dependente_beneficiario->doenca_cronica == true) {
                $doenca_cronica = 'Sim';
            } else if ($dependente_beneficiario->doenca_cronica == false) {
                $doenca_cronica = 'Não';
            }
            return [
                'Nome' => $dependente_beneficiario->nome,
                'Email' => $dependente_beneficiario->email,
                'BI' => $dependente_beneficiario->numero_identificacao,
                'Código de acesso' => !empty($dependente_beneficiario->user) ? $dependente_beneficiario->user->codigo_login : null,
                'Parantesco' => $dependente_beneficiario->parantesco,
                'Endereço' => $dependente_beneficiario->endereco,
                'Bairro' => $dependente_beneficiario->bairro,
                'Telefone' => $dependente_beneficiario->telefone,
                'Genero' => $dependente_beneficiario->genero,
                'Data de Nascimento' => $dependente_beneficiario->data_nascimento,
                'Tem doencas cronicas?' => $doenca_cronica,
                // 'doenca_cronica_nome' => $dependente_beneficiario->doenca_cronica_nome,
                // 'utilizador_activo' => $dependente_beneficiario->activo,
                'Nº do Beneficiário' => !empty($dependente_beneficiario->beneficiario) ? $dependente_beneficiario->beneficiario->numero_beneficiario : null,
                'Nome do Beneficiário' => !empty($dependente_beneficiario->beneficiario) ? $dependente_beneficiario->beneficiario->nome : "",
            ];
        });

        return $this->sendResponse($dependentes_beneficiarios->toArray(), 'Dependentes Beneficiarios retrieved successfully');
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
     * @param  \App\Models\DependenteBeneficiario  $dependenteBeneficiario
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DependenteBeneficiario  $dependenteBeneficiario
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
     * @param  \App\Models\DependenteBeneficiario  $dependenteBeneficiario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DependenteBeneficiario  $dependenteBeneficiario
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Gate::denies('gerir dependente beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $dependente_beneficiario = $this->dependente_beneficiario->find($id);

        if(empty($dependente_beneficiario)) {
            return $this->sendError('Dependente Beneficiario not found');
        }

        DB::beginTransaction();
        try {

            $dependente_beneficiario->delete();

            DB::commit();
            return $this->sendSuccess('Dependente Beneficiario deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
        
    }

    public function importFromExcel(Request $request)
    {
        $request->validate([
            'ficheiro' => 'required|mimes:xlsx,excel'
        ]);
        
        $empresa_id = $request->empresa_id;
        $beneficiario = null;
        $role_dependente_beneficiario_id = Role::where('codigo', 9)->pluck('id')->first();

        if (!$role_dependente_beneficiario_id)
            return $this->sendError('Role no Found', 404);

        
        $dependentes_beneficiarios_from_excel = Excel::toArray(new DependenteBeneficiarioImport, $request->ficheiro);
        $dependentes_beneficiarios_collection = collect($dependentes_beneficiarios_from_excel[0]);
        $dependentes_beneficiarios_input = $dependentes_beneficiarios_collection->map(function ($dependente_beneficiario_input) use ($empresa_id, $beneficiario) {
            
            $user = User::where('codigo_login', $dependente_beneficiario_input['codigo_beneficiario'])->with(['beneficiario'])->first();

            if(!empty($user)) {
                if(!empty($user->beneficiario)) {
                    $beneficiario = $user->beneficiario;
                }
            }
            
            return [
                'empresa_id' => $empresa_id,
                'nome' => key_exists('nome', $dependente_beneficiario_input) ? $dependente_beneficiario_input['nome'] : null,
                'activo' => key_exists('activo', $dependente_beneficiario_input) ? $dependente_beneficiario_input['activo'] : false,
                'numero_identificacao' => key_exists('numero_identificacao', $dependente_beneficiario_input) ? $dependente_beneficiario_input['numero_identificacao'] : null,
                'email' => key_exists('email', $dependente_beneficiario_input) ? $dependente_beneficiario_input['email'] : null,
                'endereco' => key_exists('endereco', $dependente_beneficiario_input) ? $dependente_beneficiario_input['endereco'] : null,
                'bairro' => key_exists('bairro', $dependente_beneficiario_input) ? $dependente_beneficiario_input['bairro'] : null,
                'telefone' => key_exists('telefone', $dependente_beneficiario_input) ? $dependente_beneficiario_input['telefone'] : null,
                'genero' => key_exists('genero', $dependente_beneficiario_input) ? $dependente_beneficiario_input['genero'] : null,
                'data_nascimento' => key_exists('data_nascimento', $dependente_beneficiario_input) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dependente_beneficiario_input['data_nascimento']))->format('Y-m-d') : null,
                'doenca_cronica' => key_exists('doenca_cronica', $dependente_beneficiario_input) ? $dependente_beneficiario_input['doenca_cronica'] : false,
                'doenca_cronica_nome' => key_exists('doenca_cronica_nome', $dependente_beneficiario_input) ? explode(",", $dependente_beneficiario_input['doenca_cronica_nome']) : [],
                'parantesco' => key_exists('parantesco', $dependente_beneficiario_input) ? $dependente_beneficiario_input['parantesco'] : null,
                'codigo_beneficiario' => key_exists('codigo_beneficiario', $dependente_beneficiario_input) ? $dependente_beneficiario_input['codigo_beneficiario'] : null,
                'beneficiario_id' => !empty($beneficiario) ? $beneficiario->id : null,
                // 'codigo_beneficiario' => key_exists('grupo_beneficiario', $dependente_beneficiario_input) ? $dependente_beneficiario_input['codigo_beneficiario'] : null,
            ];
        });
        $request->merge(['dependentes_beneficiarios' => $dependentes_beneficiarios_input->toArray()]);

        $request->validate([
            'dependentes_beneficiarios.*.empresa_id' => 'required|integer',
            'dependentes_beneficiarios.*.activo' => 'required|boolean',
            'dependentes_beneficiarios.*.nome' => 'required|string|max:100',
            'dependentes_beneficiarios.*.numero_identificacao' => 'nullable',
            'beneficiarios.*.email' => [
                'nullable',
                'email',
                // Rule::unique('beneficiarios')->ignore()->where('empresa_id', $empresa_id)
                "unique:dependente_beneficiarios,email,NULL,id,empresa_id,$empresa_id"
            ],
            'dependentes_beneficiarios.*.endereco' => 'required|string|max:255',
            'dependentes_beneficiarios.*.bairro' => 'required|string|max:100',
            'dependentes_beneficiarios.*.telefone' => 'nullable|max:50',
            'dependentes_beneficiarios.*.genero' => ['required', 'string', 'min:1', 'max:1', Rule::in(['F', 'M'])],
            'dependentes_beneficiarios.*.data_nascimento' => 'required|date',
            'dependentes_beneficiarios.*.doenca_cronica' => 'required|boolean',
            'dependentes_beneficiarios.*.doenca_cronica_nome' => 'required_if:doenca_cronica,1|array',
            'dependentes_beneficiarios.*.parantesco' => 'required|string',
            'dependentes_beneficiarios.*.codigo_beneficiario' => 'required|starts_with:BENE',
            'dependentes_beneficiarios.*.beneficiario_id' => 'required|integer',
            // 'beneficiarios.*.tem_dependentes' => 'required|boolean',
        ]);

        // dd($request->all());
        DB::beginTransaction();
        try {
            foreach ($dependentes_beneficiarios_input as $dependente_beneficiario_input) {
                $user = new User();
                $user->nome = $dependente_beneficiario_input['nome'];
                $user->password = bcrypt('ifarmacias'); // Default password, is changed after the created Event of Beneficiario
                $user->active = $dependente_beneficiario_input['activo'];
                $user->loged_once = 1;
                $user->login_attempts = 0;
                $user->role_id = $role_dependente_beneficiario_id;
                $user->save();

                $dependente_beneficiario_input['user_id'] = $user->id;

                $dependente_beneficiario = $this->dependente_beneficiario->create($dependente_beneficiario_input);
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        
        return $this->sendSuccess('Dependentes saved successfully', 200);
    }
}
