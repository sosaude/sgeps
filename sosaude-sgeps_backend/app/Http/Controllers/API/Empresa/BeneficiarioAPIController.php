<?php

namespace App\Http\Controllers\API\Empresa;

use Excel;
use Response;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use App\Models\DoencaCronica;
use Illuminate\Validation\Rule;
use App\Models\GrupoBeneficiario;
use Illuminate\Support\Facades\DB;
use App\Imports\BeneficiarioImport;
use Illuminate\Support\Facades\Gate;
use App\Models\DependenteBeneficiario;
use Illuminate\Database\QueryException;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\Tenant\Empresa\CreateUpdateBeneficiarioFormRequest;

/**
 * Class BeneficiarioController
 * @package App\Http\Controllers\API
 */

class BeneficiarioAPIController extends AppBaseController
{
    /** @var $beneficiario $dependente_beneficiario */
    private $user;
    private $beneficiario;
    private $dependente_beneficiario;
    private $grupo_beneficiario;
    private $doenca_cronica;

    public function __construct(User $user, Beneficiario $beneficiario, DependenteBeneficiario $dependente_beneficiario, GrupoBeneficiario $grupo_beneficiario, DoencaCronica $doenca_cronica)
    {
        $this->user = $user;
        $this->beneficiario = $beneficiario;
        $this->dependente_beneficiario = $dependente_beneficiario;
        $this->grupo_beneficiario = $grupo_beneficiario;
        $this->doenca_cronica = $doenca_cronica;
    }

    /**
     * Display a listing of the Beneficiario.
     * GET|HEAD /beneficiarios
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $empresa_id = $request->empresa_id;

        $beneficiarios = $this->beneficiario
            ->byEmpresa($empresa_id)
            ->with(
                'grupoBeneficiario:id,nome',
                'dependentes:id,activo,nome,numero_identificacao,email,parantesco,endereco,bairro,telefone,genero,data_nascimento,doenca_cronica,doenca_cronica_nome,beneficiario_id,user_id',
                'dependentes.user:id,active',
                'user:id,active,codigo_login'
            )->get()->map(function ($beneficiario) {
                // $beneficiario->utilizador_activo = $beneficiario->user->active;
                
                return [
                    'id' => $beneficiario->id,
                    'nome' => $beneficiario->nome,
                    'activo' => $beneficiario->activo,
                    'numero_identificacao' => $beneficiario->numero_identificacao,
                    'email' => $beneficiario->email,
                    'codigo_acesso' => !empty($beneficiario->user) ? $beneficiario->user->codigo_login : null,
                    'numero_beneficiario' => $beneficiario->numero_beneficiario,
                    'endereco' => $beneficiario->endereco,
                    'bairro' => $beneficiario->bairro,
                    'telefone' => $beneficiario->telefone,
                    'genero' => $beneficiario->genero,
                    'data_nascimento' => $beneficiario->data_nascimento,
                    'ocupacao' => $beneficiario->ocupacao,
                    'utilizador_activo' => $beneficiario->activo,
                    'aposentado' => $beneficiario->aposentado,
                    'tem_dependentes' => $beneficiario->tem_dependentes,
                    'doenca_cronica' => $beneficiario->doenca_cronica,
                    'doenca_cronica_nome' => $beneficiario->doenca_cronica_nome,
                    'grupo_beneficiario_id' => $beneficiario->grupo_beneficiario_id,
                    'grupoBeneficiario' => $beneficiario->grupoBeneficiario,
                    'dependentes' => $beneficiario->dependentes->map(function ($dependente) {
                        return [
                            'id' => $dependente->id,
                            'nome' => $dependente->nome,
                            'numero_identificacao' => $dependente->numero_identificacao,
                            'email' => $dependente->email,
                            'parantesco' => $dependente->parantesco,
                            'endereco' => $dependente->endereco,
                            'bairro' => $dependente->bairro,
                            'telefone' => $dependente->telefone,
                            'genero' => $dependente->genero,
                            'data_nascimento' => $dependente->data_nascimento,
                            'doenca_cronica' => $dependente->doenca_cronica,
                            'doenca_cronica_nome' => $dependente->doenca_cronica_nome,
                            'beneficiario_id' => $dependente->beneficiario_id,
                            'utilizador_activo' => $dependente->activo
                        ];
                    }),
                ];
                // return $beneficiario->only('id', 'nome', 'numero_beneficiario', 'endereco', 'bairro', 'telefone', 'genero', 'data_nascimento', 'ocupacao', 'aposentado', 'tem_dependentes', 'doenca_cronica', 'doenca_cronica_nome', 'grupo_beneficiario_id', 'utilizador_activo', 'dependentes', 'grupoBeneficiario');
            });



        return $this->sendResponse($beneficiarios->toArray(), 'Beneficiarios retrieved successfully');
    }
    public function indexBeneficiario(Request $request)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        $empresa_id = $request->empresa_id;

        $beneficiarios = $this->beneficiario
            ->byEmpresa($empresa_id)
            ->with(
                'grupoBeneficiario:id,nome',
                'dependentes:id,activo,nome,numero_identificacao,email,parantesco,endereco,bairro,telefone,genero,data_nascimento,doenca_cronica,doenca_cronica_nome,beneficiario_id,user_id',
                'dependentes.user:id,active',
                'user:id,active,codigo_login'
            )->get()->map(function ($beneficiario) {
                // $beneficiario->utilizador_activo = $beneficiario->user->active;

                
                if ($beneficiario->activo == true) {
                    $activo = 'Sim';
                } else if ($beneficiario->activo == false) {
                    $activo = 'Não';
                }

                if ($beneficiario->aposentado == true) {
                    $aposentado = 'Sim';
                } else if ($beneficiario->aposentado == false) {
                    $aposentado = 'Não';
                }

                if ($beneficiario->tem_dependentes == true) {
                    $tem_dependentes = 'Sim';
                } else if ($beneficiario->tem_dependentes == false) {
                    $tem_dependentes = 'Não';
                }

                if ($beneficiario->doenca_cronica == true) {
                    $doenca_cronica = 'Sim';
                } else if ($beneficiario->doenca_cronica == false) {
                    $doenca_cronica = 'Não';
                }
                
                return [
                    'Nome' => $beneficiario->nome,
                    'Activo' => $activo,
                    'Email' => $beneficiario->email,
                    'Código de acesso' => !empty($beneficiario->user) ? $beneficiario->user->codigo_login : null,
                    'Nº do Beneficiário' => $beneficiario->numero_beneficiario,
                    'BI' => $beneficiario->numero_identificacao,
                    'Endereço' => $beneficiario->endereco,
                    'Bairro' => $beneficiario->bairro,
                    'Telefone' => $beneficiario->telefone,
                    'Genero' => $beneficiario->genero,
                    'Data de Nascimento' => $beneficiario->data_nascimento,
                    'Ocupção' => $beneficiario->ocupacao,
                    'Aposentado' => $aposentado,
                    'Tem dependentes?' => $tem_dependentes,
                    'Tem doenças crónicas?' => $doenca_cronica,
                ];
            });



        return $this->sendResponse($beneficiarios->toArray(), 'Beneficiarios retrieved successfully');
    }

    /**
     * Retrieve a listing of resources used to create the UtilizadorEmpresa.
     * GET|HEAD /beneficiarios/create
     *
     * @return Response
     */
    public function create()
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');

        $grupos_beneficiario = $this->grupo_beneficiario->byEmpresa($empresa_id)->select('id', 'nome')->get();
        $doencas_cronicas = $this->doenca_cronica->orderBy('nome', 'ASC')->get(['id', 'nome']);

        $data = [
            'grupos_beneficiario' => $grupos_beneficiario,
            'doencas_cronicas' => $doencas_cronicas
        ];

        return $this->sendResponse($data, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created Beneficiario in storage.
     * POST /beneficiarios
     *
     * @param CreateUpdateBeneficiarioFormRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateBeneficiarioFormRequest $request)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $input = $request->all();
        // dd($input);
        $dependentes_beneficiario_input = $input['dependentes'];

        $result_extra_beneficiario_validation = $this->extraBeneficiarioValidation($input);
        if (sizeof($result_extra_beneficiario_validation) > 0)
            return $this->sendErrorValidation($result_extra_beneficiario_validation, 422);

        // dd($request->all());

        $role_id = Role::where('codigo', 8)->pluck('id')->first();
        if (!$role_id)
            return $this->sendError('Role no Found', 404);

        // dd($input);
        // dd($dependentes_beneficiario_input);
        DB::beginTransaction();
        try {

            $user = new User();
            $user->nome = $request->nome;
            $user->password = bcrypt('ifarmacias'); // Default password, is changed after the created Event of Beneficiario
            $user->active = $request->utilizador_activo;
            $user->loged_once = 1;
            $user->login_attempts = 0;
            $user->role_id = $role_id;
            $user->save();
            $input['user_id'] = $user->id;
            $input['activo'] = $request->utilizador_activo;

            /** @var Beneficiario $beneficiario */
            $beneficiario = $this->beneficiario->create($input);


            if ($input['tem_dependentes'] == 1) {

                if ($dependentes_beneficiario_input) {
                    foreach ($dependentes_beneficiario_input as $dependente_beneficiario_input) {
                        $dependente_beneficiario_input['beneficiario_id'] = $beneficiario->id;
                        $dependente_beneficiario_input['empresa_id'] = $request->empresa_id;

                        $role_benef_dependente_id = Role::where('codigo', 9)->pluck('id')->first();
                        $user_benef_dependente = new User();
                        $user_benef_dependente->nome = $dependente_beneficiario_input['nome'];
                        $user_benef_dependente->password = bcrypt('ifarmacias'); // Default password, is changed after the created Event of Beneficiario
                        $user_benef_dependente->active = $dependente_beneficiario_input['utilizador_activo'];
                        $user_benef_dependente->loged_once = 0;
                        $user_benef_dependente->login_attempts = 0;
                        $user_benef_dependente->role_id = $role_benef_dependente_id;
                        $user_benef_dependente->save();

                        $dependente_beneficiario_input['user_id'] = $user_benef_dependente->id;
                        $dependente_beneficiario_input['activo'] = $dependente_beneficiario_input['utilizador_activo'];
                        $this->dependente_beneficiario->create($dependente_beneficiario_input);
                    }
                }
            }

            DB::commit();
            return $this->sendResponse($beneficiario->toArray(), 'Beneficiario saved successfully', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified Beneficiario.
     * GET|HEAD /beneficiarios/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');

        /** @var Beneficiario $beneficiario */
        $beneficiario = $this->beneficiario
            ->byEmpresa($empresa_id)
            ->with(
                'grupoBeneficiario:id,nome',
                'dependentes:id,activo,nome,numero_identificacao,email,parantesco,endereco,bairro,telefone,genero,data_nascimento,doenca_cronica,doenca_cronica_nome,beneficiario_id'
            )
            ->where('id', $id)
            ->first();

        if (empty($beneficiario)) {
            return $this->sendError('Beneficiario not found');
        }

        $data = [
            'id' => $beneficiario->id,
            'numero_beneficiario' => $beneficiario->numero_beneficiario,
            'numero_identificacao' => $beneficiario->numero_identificacao,
            'email' => $beneficiario->email,
            'endereco' => $beneficiario->endereco,
            'bairro' => $beneficiario->bairro,
            'telefone' => $beneficiario->telefone,
            'genero' => $beneficiario->genero,
            'data_nascimento' => $beneficiario->data_nascimento,
            'ocupacao' => $beneficiario->ocupacao,
            'aposentado' => $beneficiario->aposentado,
            'tem_dependentes' => $beneficiario->tem_dependentes,
            'doenca_cronica' => $beneficiario->doenca_cronica,
            'doenca_cronica_nome' => $beneficiario->doenca_cronica_nome,
            'utilizador_activo' => $beneficiario->activo,
            'grupoBeneficiario' => $beneficiario->grupoBeneficiario,
            'grupoBeneficiario' => $beneficiario->grupoBeneficiario,
            'dependentes' => $beneficiario->dependentes,
        ];

        return $this->sendResponse($data, 'Beneficiario retrieved successfully');
    }

    /**
     * Update the specified Beneficiario in storage.
     * PUT/PATCH /beneficiarios/{id}
     *
     * @param int $id
     * @param CreateUpdateBeneficiarioFormRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateBeneficiarioFormRequest $request)
    {

        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $input = $request->all();

        $result_extra_beneficiario_validation = $this->extraBeneficiarioValidation($input);
        if (sizeof($result_extra_beneficiario_validation) > 0)
            return $this->sendErrorValidation($result_extra_beneficiario_validation, 422);

        $input['activo'] = $request->utilizador_activo;
        $empresa_id = $request->empresa_id;
        $dependentes_beneficiario_input = $input['dependentes'];

        $beneficiario = $this->beneficiario->byEmpresa($empresa_id)->find($id);
        if (empty($beneficiario))
            return $this->sendError('Beneficiario not found', 404);

        DB::beginTransaction();
        try {
            // Update the $beneficiario
            $beneficiario->update($input);

            if ($input['tem_dependentes'] == 1) {

                if ($dependentes_beneficiario_input) {

                    foreach ($dependentes_beneficiario_input as $dependente_beneficiario_input) {
                        $dependente_beneficiario_input['beneficiario_id'] = $beneficiario->id;
                        $dependente_beneficiario_input['activo'] = $dependente_beneficiario_input['utilizador_activo'];

                        if (!empty($dependente_beneficiario_input['id'])) {

                            $dependente_beneficiario = $this->dependente_beneficiario
                                ->with('user')
                                ->byEmpresa($empresa_id)
                                ->where('beneficiario_id', $beneficiario->id)
                                ->find($dependente_beneficiario_input['id']);
                            if (empty($dependente_beneficiario)) {
                                DB::rollback();
                                return $this->sendError('Dependente Beneficiario não encontrado.', 404);
                            }

                            if (!empty($dependente_beneficiario->user)) {
                                $user = $dependente_beneficiario->user;
                            } else {
                                DB::rollback();
                                return $this->sendError('Usuário do Dependente Beneficiario não encontrado.', 404);
                            }

                            $dependente_beneficiario->update($dependente_beneficiario_input);
                            $user->update(['active' => $dependente_beneficiario_input['utilizador_activo']]);
                        } else {

                            $role_benef_dependente_id = Role::where('codigo', 9)->pluck('id')->first();
                            $user_benef_dependente = new User();
                            $user_benef_dependente->nome = $dependente_beneficiario_input['nome'];
                            $user_benef_dependente->password = bcrypt('ifarmacias'); // Default password, is changed after the created Event of Beneficiario
                            $user_benef_dependente->active = $dependente_beneficiario_input['utilizador_activo'];
                            $user_benef_dependente->loged_once = 0;
                            $user_benef_dependente->login_attempts = 0;
                            $user_benef_dependente->role_id = $role_benef_dependente_id;
                            $user_benef_dependente->save();

                            $dependente_beneficiario_input['beneficiario_id'] = $beneficiario->id;
                            $dependente_beneficiario_input['empresa_id'] = $request->empresa_id;
                            $dependente_beneficiario_input['user_id'] = $user_benef_dependente->id;
                            $dependente_beneficiario_input['activo'] = $dependente_beneficiario_input['utilizador_activo'];
                            $dependente_beneficiario = $this->dependente_beneficiario->create($dependente_beneficiario_input);
                        }




                        // $dependente_beneficiario->update($dependente_beneficiario_input);
                    }
                }
            }
            // dd('passou');
            DB::commit();
            return $this->sendResponse($beneficiario->toArray(), 'Beneficiario updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified Beneficiario from storage.
     * DELETE /beneficiarios/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        if (Gate::denies('gerir beneficiário')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $empresa_id = request('empresa_id');

        /** @var Beneficiario $beneficiario */
        $beneficiario = $this->beneficiario->find($id);

        if (empty($beneficiario)) {
            return $this->sendError('Beneficiario not found');
        }

        DB::beginTransaction();
        try {

            $user = $this->user->find($beneficiario->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('Usuário do Beneficiario não encontrado');
            }

            $this->dependente_beneficiario->byEmpresa($empresa_id)->where('beneficiario_id', $beneficiario->id)->delete();

            $beneficiario->delete();
            $user->delete();

            DB::commit();
            return $this->sendSuccess('Beneficiario deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        } catch (QueryException $e) {
            DB::rollback();
            return $this->sendError('Erro de integridade referêncial na Base de Dados! Contacte o Administrador.', 500);
        }
    }

    protected function extraBeneficiarioValidation(array $input)
    {
        $errors = [];
        $activo_dependente_validation = $this->validarActivoDependente($input);
        $email_dependente_validation = $this->validarUniqueMailDependente($input);

        $errors = array_merge($errors, $activo_dependente_validation, $email_dependente_validation);

        return $errors;
    }

    protected function validarActivoDependente(array $input)
    {
        $beneficiario_activo = $input['utilizador_activo'];
        $dependentes_input = $input['dependentes'];
        $errors = [];

        if ($beneficiario_activo == false) {
            foreach ($dependentes_input as $key => $dependente_input) {
                if ($dependente_input['utilizador_activo'] == true)
                    $errors["dependentes.$key.utilizador_activo"] = ['O Dependente não pode assumir o valor verdadeiro para o campo utilizador_activo enquanto o seu Beneficiário assumir o valor falso para o campo utilizador_activo!'];
            }
        }

        return $errors;
    }

    protected function validarUniqueMailDependente(array $input)
    {
        $dependentes_input = $input['dependentes'];
        $emails = [];
        $errors = [];

        foreach ($dependentes_input as $key => $dependente_input) {
            if (isset($dependente_input['email'])) {
                array_push($emails, strtolower($dependente_input['email']));
            }

            if (isset($dependente_input['id']) && isset($dependente_input['email'])) {
                $email = DependenteBeneficiario::where('email', $dependente_input['email'])->whereNotIn('id', [$dependente_input['id']])->count();

                if ($email > 0) {
                    $errors["dependentes.$key.email"] = ['O email informado já foi usado!'];
                }
            } else if (isset($dependente_input['email'])) {
                $email = DependenteBeneficiario::where('email', $dependente_input['email'])->count();

                if ($email > 0) {
                    $errors["dependentes.$key.email"] = ['O email informado já foi usado!'];
                }
            }
        }

        foreach ($dependentes_input as $key => $dependente_input) {
            if (isset($dependente_input['email'])) {
                if ($this->countOccurrences($emails, $dependente_input['email']) > 1) {
                    $errors["dependentes.$key.email"] = ['Este email está sendo informado para mais de um dependente!'];
                }
            }
        }

        return $errors;
    }

    protected function countOccurrences($array, $value)
    {
        $res = 0;
        foreach ($array as $element) {
            if (strtolower($value) == strtolower($element))
                $res++;
        }

        // dd($res);
        return $res;
    }

    public function importFromExcel(Request $request)
    {
        $request->validate([
            'ficheiro' => 'required|mimes:xlsx,excel'
        ]);

        $empresa_id = $request->empresa_id;
        $role_beneficiario_id = Role::where('codigo', 8)->pluck('id')->first();
        $role_dependente_beneficiario_id = Role::where('codigo', 9)->pluck('id')->first();

        if (!$role_beneficiario_id)
            return $this->sendError('Role no Found', 404);

        if (!$role_dependente_beneficiario_id)
            return $this->sendError('Role no Found', 404);

        
        $beneficiarios_from_excel = Excel::toArray(new BeneficiarioImport, $request->ficheiro);
        $beneficiarios_collection = collect($beneficiarios_from_excel[0]);
        
        $beneficiarios_input = $beneficiarios_collection->map(function ($beneficiario_input) use ($empresa_id) {
            return [
                'empresa_id' => $empresa_id,
                'nome' => key_exists('nome', $beneficiario_input) ? $beneficiario_input['nome'] : null,
                'activo' => key_exists('activo', $beneficiario_input) ? $beneficiario_input['activo'] : false,
                'numero_identificacao' => key_exists('numero_identificacao', $beneficiario_input) ? $beneficiario_input['numero_identificacao'] : null,
                'email' => key_exists('email', $beneficiario_input) ? $beneficiario_input['email'] : null,
                'numero_beneficiario' => key_exists('numero_beneficiario', $beneficiario_input) ? $beneficiario_input['numero_beneficiario'] : null,
                'endereco' => key_exists('endereco', $beneficiario_input) ? $beneficiario_input['endereco'] : null,
                'bairro' => key_exists('bairro', $beneficiario_input) ? $beneficiario_input['bairro'] : null,
                'telefone' => key_exists('telefone', $beneficiario_input) ? $beneficiario_input['telefone'] : null,
                'genero' => key_exists('genero', $beneficiario_input) ? $beneficiario_input['genero'] : null,
                'data_nascimento' => key_exists('data_nascimento', $beneficiario_input) ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($beneficiario_input['data_nascimento']))->format('Y-m-d') : null,
                'ocupacao' => key_exists('ocupacao', $beneficiario_input) ? $beneficiario_input['ocupacao'] : null,
                'aposentado' => key_exists('aposentado', $beneficiario_input) ? $beneficiario_input['aposentado'] : false,
                'tem_dependentes' => key_exists('tem_dependentes', $beneficiario_input) ? $beneficiario_input['tem_dependentes'] : false,
                'doenca_cronica' => key_exists('doenca_cronica', $beneficiario_input) ? $beneficiario_input['doenca_cronica'] : false,
                'doenca_cronica_nome' => key_exists('doenca_cronica_nome', $beneficiario_input) ? explode(",", $beneficiario_input['doenca_cronica_nome']) : [],
                'grupo_beneficiario_id' => key_exists('grupo_beneficiario', $beneficiario_input) ? GrupoBeneficiario::where([['nome', $beneficiario_input['grupo_beneficiario']], ['empresa_id', $empresa_id]])->pluck('id')->first() : null,
            ];
        });
        $request->merge(['beneficiarios' => $beneficiarios_input->toArray()]);

        $request->validate([
            'beneficiarios.*.empresa_id' => 'required|integer',
            'beneficiarios.*.activo' => 'required|boolean',
            'beneficiarios.*.nome' => 'required|string|max:100',
            'beneficiarios.*.numero_identificacao' => 'nullable',
            'beneficiarios.*.numero_beneficiario' => [
                'nullable',
                'string',
                'max:100',
                "unique:beneficiarios,numero_beneficiario,NULL,id,empresa_id,$empresa_id"
            ],
            'beneficiarios.*.email' => [
                'nullable',
                'email',
                // Rule::unique('beneficiarios')->ignore()->where('empresa_id', $empresa_id)
                "unique:beneficiarios,email,NULL,id,empresa_id,$empresa_id"
            ],
            'beneficiarios.*.endereco' => 'required|string|max:255',
            'beneficiarios.*.bairro' => 'required|string|max:100',
            'beneficiarios.*.telefone' => 'nullable|max:50',
            'beneficiarios.*.genero' => ['required', 'string', 'min:1', 'max:1', Rule::in(['F', 'M'])],
            'beneficiarios.*.data_nascimento' => 'required|date',
            'beneficiarios.*.ocupacao' => 'required|string|max:100',
            'beneficiarios.*.aposentado' => 'required|boolean',
            'beneficiarios.*.doenca_cronica' => 'required|boolean',
            'beneficiarios.*.doenca_cronica_nome' => 'required_if:doenca_cronica,1|array',
            'beneficiarios.*.grupo_beneficiario_id' => 'required|integer',
            // 'beneficiarios.*.tem_dependentes' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            foreach ($beneficiarios_input as $beneficiario_input) {
                $user = new User();
                $user->nome = $beneficiario_input['nome'];
                $user->password = bcrypt('ifarmacias'); // Default password, is changed after the created Event of Beneficiario
                $user->active = $beneficiario_input['activo'];
                $user->loged_once = 1;
                $user->login_attempts = 0;
                $user->role_id = $role_beneficiario_id;
                $user->save();

                $beneficiario_input['user_id'] = $user->id;

                $beneficiario = $this->beneficiario->create($beneficiario_input);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }

        DB::commit();
        return $this->sendSuccess('Beneficiarios saved successfully', 200);
    }
}
