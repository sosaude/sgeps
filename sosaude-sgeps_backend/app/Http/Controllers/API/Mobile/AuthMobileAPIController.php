<?php

namespace App\Http\Controllers\API\Mobile;

use JWTAuth;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Provincia;
use Illuminate\Support\Str;
use App\Models\Beneficiario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendResetClientePasswordJob;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\AppBaseController;
use App\Notifications\SendResetClientePasswordNotification;

class AuthMobileAPIController extends AppBaseController
{
    //
    private $cliente;
    private $beneficiario;
    private $user;
    private $provincia;

    public function __construct(Cliente $cliente, Beneficiario $beneficiario, User $user, Provincia $provincia)
    {
        $this->cliente = $cliente;
        $this->beneficiario = $beneficiario;
        $this->user = $user;
        $this->provincia = $provincia;
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (!$token = JWTAuth::attempt(['email' => strtolower($request->email), 'password' => $request->password])) {
            return $this->sendError('Credenciais inválidas ou usuário inactivo!', 400);
        }

        $cliente = Auth::user();
        $user = null;
        $beneficiario_id = null;
        $codigo_login_beneficiario = null;
        $dependente_beneficiario_id = null;
        $codigo_login_dependente_beneficiario = null;
        $empresa_id = null;
        $empresa_nome = null;

        $cliente->load(['beneficiario:id,empresa_id,user_id', 'beneficiario.empresa:id,nome', 'dependenteBeneficiario:id,empresa_id,user_id', 'dependenteBeneficiario.empresa:id,nome']);
        // dd($cliente);
        if (!empty($beneficiario = $cliente->beneficiario)) {
            $user = User::find($beneficiario->user_id);
            $beneficiario_id = $beneficiario->id;
            $codigo_login_beneficiario = !empty($user) ? $user->codigo_login : null;

            if (!empty($empresa = $beneficiario->empresa)) {
                $empresa_id = $empresa->id;
                $empresa_nome = $empresa->nome;
            }
        } elseif (!empty($dependente_beneficiario = $cliente->dependenteBeneficiario)) {

            $user = User::find($dependente_beneficiario->user_id);
            $dependente_beneficiario_id = $dependente_beneficiario->id;
            $codigo_login_dependente_beneficiario = !empty($user) ? $user->codigo_login : null;

            if (!empty($empresa = $dependente_beneficiario->empresa)) {
                $empresa_id = $empresa->id;
                $empresa_nome = $empresa->nome;
            }
        }

        $data = [
            'id' => $cliente->id,
            'logado_uma_vez' => $cliente->logado_uma_vez,
            'nome' => $cliente->nome,
            'peso' => $cliente->peso,
            'altura' => $cliente->altura,
            'tem_doenca_cronica' => $cliente->tem_doenca_cronica,
            'doenca_cronica_nome' => $cliente->doenca_cronica_nome,
            'tipo_sanguineo' => $cliente->tipo_sanguineo,
            'provincia' => $cliente->provincia,
            'cidade' => $cliente->cidade,
            'foto_perfil' => $cliente->foto_perfil,
            'beneficiario_id' => $beneficiario_id,
            'codigo_login_beneficiario' => $codigo_login_beneficiario,
            'dependente_beneficiario_id' => $dependente_beneficiario_id,
            'codigo_login_dependente_beneficiario' => $codigo_login_dependente_beneficiario,
            'empresa_id' => $empresa_id,
            'empresa_nome' => $empresa_nome,
        ];

        return response()->json(['token' => $token, 'expires_in' => $this->guard()->factory()->getTTL(), 'cliente' => $data], 200);
    }

    public function criar()
    {
        $provincias = $this->provincia->orderBy('nome', 'ASC')->get(['id', 'nome']);

        $data = [
            'provincias' => $provincias,
        ];

        return $this->sendResponse($data, 'Recursos para o auto-registo devolvidos com sucesso!', 200);
    }

    public function autoRegistarFaseUm(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            // 'numero_identificacao' => 'nullable|string|max:50',
            'email' => 'required|email|unique:clientes,email',
            'password' => 'required|string|min:7',
        ]);

        $email = strtolower($request->email);
        $password_plana = $request->password;

        $input['nome'] = $request->nome;
        // $input['numero_identificacao'] = $request->numero_identificacao;
        $input['email'] = $email;
        $input['password'] = bcrypt($password_plana);
        $input['activo'] = true;
        $input['logado_uma_vez'] = true;
        // dd($input);
        DB::beginTransaction();
        try {
            $cliente = $this->cliente->create($input);

            if (!$token = JWTAuth::attempt(['email' => strtolower($request->email), 'password' => $password_plana])) {
                DB::rollback();
                return $this->sendError('Não foi possível criar o usuário!', 400);
            }

            $data = [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
                'logado_uma_vez' => $cliente->logado_uma_vez,
            ];

            DB::commit();
            return response()->json(['token' => $token, 'expires_in' => $this->guard()->factory()->getTTL(), 'cliente' => $data], 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    public function autoRegistarFaseDoisAntesDaActualizacaoDoDependente(Request $request)
    {
        $provincias = ['Maputo Cidade', 'Maputo Provincia', 'Gaza', 'Inhambane', 'Sofala', 'Manica', 'Zambezia', 'Tete', 'Nampula', 'Cabo Delgado', 'Niassa'];
        $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'nome' => 'nullable|string|max:255',
            'peso' => 'nullable|numeric',
            'altura' => 'nullable|numeric',
            'e_benefiairio_plano_saude' => 'nullable|boolean',
            'beneficiario_id' => 'nullable|required_if:e_benefiairio_plano_saude,1|integer|exists:beneficiarios,id',
            'tem_doenca_cronica' => 'required|boolean',
            'doenca_cronica_nome' => 'nullable|array|required_if:tem_doenca_cronica,1',
            'tipo_sanguineo' => ['nullable', 'string', Rule::in(['A+', 'B+', 'A-', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'provincia' => ['required', 'string', Rule::in($provincias)],
            'cidade' => 'required|string|max:100',
        ]);

        $input = $request->only(['nome', 'peso', 'altura', 'tem_doenca_cronica', 'doenca_cronica_nome', 'tipo_sanguineo', 'provincia', 'cidade']);
        if (!empty($request->e_benefiairio_plano_saude)) {
            $input['e_benefiairio_plano_saude'] = $request->e_benefiairio_plano_saude;
        } // ao informar e_benefiairio_plano_saude = false, este não será capturado no $input[], mas a DB já está com default=false para este campo


        if (!empty($request->beneficiario_id)) {
            if ($request->e_benefiairio_plano_saude != true) {
                $error['e_benefiairio_plano_saude'] = ['Ao informar o beneficiario_id torna-se necessário informar o valor do campo e_benefiairio_plano_saude como true'];
                return $this->sendErrorValidation($error, 422);
            }
            $input['beneficiario_id'] = $request->beneficiario_id;
        }


        $cliente = $this->cliente->with('beneficiario')->find($request->cliente_id);
        if (empty($cliente))
            return $this->sendError('Cliente não encontrado!', 404);



        if (!empty($request->beneficiario_id)) {

            if (!empty($cliente->beneficiario)) {
                if ($cliente->beneficiario_id != $request->beneficiario_id)
                    return $this->sendError('A conta do Beneficiário informada não corresponde com a actual conta associada!', 404);
            }

            $cliente_id_array = [$cliente->id];
            $cliente_verificacao = $this->cliente
                ->where('beneficiario_id', $request->beneficiario_id)
                ->whereNotIn('id', $cliente_id_array)
                ->count();
            if (!empty($cliente_verificacao))
                return $this->sendError('A conta do Beneficiário informada já encontra-se em uso!', 404);
        }


        // dd($input);
        DB::beginTransaction();
        try {
            $cliente->fill($input);
            $cliente->save();

            $cliente->load('beneficiario');
            $user = null;
            if (!empty($cliente->beneficiario)) {
                $user = User::find($cliente->beneficiario->user_id);
            }

            $data = [
                'id' => $cliente->id,
                'logado_uma_vez' => $cliente->logado_uma_vez,
                'nome' => $cliente->nome,
                'peso' => $cliente->peso,
                'altura' => $cliente->altura,
                'tem_doenca_cronica' => $cliente->tem_doenca_cronica,
                'doenca_cronica_nome' => $cliente->doenca_cronica_nome,
                'tipo_sanguineo' => $cliente->tipo_sanguineo,
                'provincia' => $cliente->provincia,
                'cidade' => $cliente->cidade,
                'foto_perfil' => $cliente->foto_perfil,
                'beneficiario_id' => !empty($cliente->beneficiario) ? $cliente->beneficiario_id : null,
                'codigo_login_beneficiario' => !empty($user) ? $user->codigo_login : null,
                'empresa_id' => !empty($cliente->beneficiario) ? $cliente->beneficiario->empresa_id : null,
                'empresa_nome' => !empty($cliente->beneficiario->empresa) ? $cliente->beneficiario->empresa->nome : null,
            ];

            DB::commit();
            return $this->sendResponse($data, '', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }


    public function autoRegistarFaseDois(Request $request)
    {
        // dd($request->doenca_cronica_nome);
        // $type = gettype($request->doenca_cronica_nome);
        // // dd($type);
        // $data = [
        //     'tipo_de_dado' => $type
        // ];
        // return $this->sendResponse($data, 'Fazendo Debug das Doenças crónicas');
        // $campos = [
        //     'campos' => $request->all()
        // ];

        // return $this->sendResponse($campos, 'Fazendo Debug das Doenças crónicas');

        if (isset($request->doenca_cronica_nome)) {
            if (gettype($request->doenca_cronica_nome) != 'array') {
                $doenca_cronica__nome_array = explode(";", $request->doenca_cronica_nome);
                $request['doenca_cronica_nome'] = $doenca_cronica__nome_array;
            }
        }


        $provincias = ['Maputo Cidade', 'Maputo Provincia', 'Gaza', 'Inhambane', 'Sofala', 'Manica', 'Zambezia', 'Tete', 'Nampula', 'Cabo Delgado', 'Niassa'];
        $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'nome' => 'nullable|string|max:255',
            // 'numero_identificacao' => 'nullable|string|max:50',
            'peso' => 'nullable|numeric',
            'altura' => 'nullable|numeric',
            'e_benefiairio_plano_saude' => 'nullable|boolean', //DEVE SER REQUIRED ESTE CAMPO
            'tem_doenca_cronica' => 'nullable|boolean',
            'doenca_cronica_nome' => 'nullable|array|required_if:tem_doenca_cronica,1',
            'tipo_sanguineo' => ['nullable', 'string', Rule::in(['A+', 'B+', 'A-', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'provincia' => ['nullable', 'string', Rule::in($provincias)],
            'cidade' => 'nullable|string|max:100',
            'beneficiario_id' =>
            [
                function ($attribute, $value, $fail) {
                    if (isset(request()->beneficiario_id) && isset(request()->dependente_beneficiario_id)) {
                        $fail('Não podem ser informados o beneficiario_id e dependente_beneficiario_id em simultâneo');
                    }
                    if (!isset(request()->beneficiario_id) && !isset(request()->dependente_beneficiario_id) && request()->e_benefiairio_plano_saude != false) {
                        $fail($attribute . ' é obrigatorio quando e_benefiairio_plano_saude for verdadeiro e dependente_beneficiario_id não tiver sido informado!');
                    }
                }
            ],
            'dependente_beneficiario_id' =>
            [
                function ($attribute, $value, $fail) {
                    if (isset(request()->dependente_beneficiario_id) && isset(request()->beneficiario_id)) {
                        $fail('Não podem ser informados o dependente_beneficiario_id e beneficiario_id em simultâneo');
                    }
                    if (!isset(request()->dependente_beneficiario_id) && !isset(request()->beneficiario_id) && request()->e_benefiairio_plano_saude != false) {
                        $fail($attribute . ' é obrigatorio quando e_benefiairio_plano_saude for verdadeiro e beneficiario_id não tiver sido informado!');
                    }
                }
            ],
        ]);
        $request->validate([
            'beneficiario_id' => 'nullable|integer',
            'dependente_beneficiario_id' => 'nullable|integer',
        ]);

        $input = $request->only(['nome','peso', 'altura', 'tem_doenca_cronica', 'doenca_cronica_nome', 'tipo_sanguineo', 'provincia', 'cidade']);
        // $input = $request->only(['nome', 'numero_identificacao', 'peso', 'altura', 'tem_doenca_cronica', 'doenca_cronica_nome', 'tipo_sanguineo', 'provincia', 'cidade']);
        if (!empty($request->e_benefiairio_plano_saude)) {
            $input['e_benefiairio_plano_saude'] = $request->e_benefiairio_plano_saude;
        } // ao informar e_benefiairio_plano_saude = false, este não será capturado no $input[], mas a DB já está com default=false para este campo

        $cliente = null;

        // Se estiver associando um Beneficiario
        if (!empty($request->beneficiario_id)) {

            if ($request->e_benefiairio_plano_saude != true) {
                $error['e_benefiairio_plano_saude'] = ['Ao informar o beneficiario_id torna-se necessário informar o valor do campo e_benefiairio_plano_saude como verdadeiro'];
                return $this->sendErrorValidation($error, 422);
            }
            $input['beneficiario_id'] = $request->beneficiario_id;

            $cliente = $this->cliente->with(['beneficiario', 'dependenteBeneficiario'])->find($request->cliente_id);
            if (empty($cliente))
                return $this->sendError('Cliente não encontrado!', 404);

            if (!empty($cliente->dependenteBeneficiario))
                return $this->sendError('A conta Cliente encontra-se associada à um Dependente!', 404);

            if (!empty($cliente->beneficiario)) {
                if ($cliente->beneficiario_id != $request->beneficiario_id)
                    return $this->sendError('A conta do Beneficiário informada não corresponde com a actual conta associada!', 404);
            } else {
                $cliente_verificacao = $this->cliente
                    ->where('beneficiario_id', $request->beneficiario_id)->first();
                if (!empty($cliente_verificacao))
                    return $this->sendError('A conta do Beneficiário informada já encontra-se em uso!', 404);
            }
        } elseif (!empty($request->dependente_beneficiario_id)) {

            if ($request->e_benefiairio_plano_saude != true) {
                $error['e_benefiairio_plano_saude'] = ['Ao informar o dependente_beneficiario_id torna-se necessário informar o valor do campo e_benefiairio_plano_saude como verdadeiro'];
                return $this->sendErrorValidation($error, 422);
            }
            $input['dependente_beneficiario_id'] = $request->dependente_beneficiario_id;

            $cliente = $this->cliente->with(['dependenteBeneficiario', 'beneficiario'])->find($request->cliente_id);
            if (empty($cliente))
                return $this->sendError('Cliente não encontrado!', 404);

            if (!empty($cliente->beneficiario))
                return $this->sendError('A conta Cliente encontra-se associada à um Beneficiário!', 404);

            if (!empty($cliente->dependenteBeneficiario)) {
                if ($cliente->dependente_beneficiario_id != $request->dependente_beneficiario_id)
                    return $this->sendError('A conta do Dependente informada não corresponde com a actual conta associada!', 404);
            } else {
                $cliente_verificacao = $this->cliente
                    ->where('dependente_beneficiario_id', $request->dependente_beneficiario_id)->first();
                if (!empty($cliente_verificacao))
                    return $this->sendError('A conta do Dependente informada já encontra-se em uso!', 404);
            }
        } else {
            $cliente = $this->cliente->find($request->cliente_id);
        }

        // dd($input);
        if (empty($cliente)) {
            return $this->sendError('Cliente não encontrado!', 404);
        }




        // dd($request->all());
        DB::beginTransaction();
        try {
            $cliente->fill($input);
            $cliente->save();

            $cliente->load(['beneficiario:id,empresa_id,user_id', 'beneficiario.empresa:id,nome', 'dependenteBeneficiario:id,empresa_id,user_id', 'dependenteBeneficiario.empresa:id,nome']);
            $user_beneficiario = null;
            $user_dependente_beneficiario = null;
            $empresa_id = null;
            $empresa_nome  = null;

            if (!empty($cliente->beneficiario)) {
                $user_beneficiario = User::find($cliente->beneficiario->user_id);
                $empresa_id = $cliente->beneficiario->empresa_id;
                $empresa_nome  = $cliente->beneficiario->empresa->nome;
            } else if (!empty($cliente->dependenteBeneficiario)) {
                $user_dependente_beneficiario = User::find($cliente->dependenteBeneficiario->user_id);
                $empresa_id  = $cliente->dependenteBeneficiario->empresa_id;
                $empresa_nome  = $cliente->dependenteBeneficiario->empresa->nome;
            }

            $data = [
                'id' => $cliente->id,
                'logado_uma_vez' => $cliente->logado_uma_vez,
                'nome' => $cliente->nome,
                'peso' => $cliente->peso,
                'altura' => $cliente->altura,
                'tem_doenca_cronica' => $cliente->tem_doenca_cronica,
                'doenca_cronica_nome' => $cliente->doenca_cronica_nome,
                'tipo_sanguineo' => $cliente->tipo_sanguineo,
                'provincia' => $cliente->provincia,
                'cidade' => $cliente->cidade,
                'foto_perfil' => $cliente->foto_perfil,
                'beneficiario_id' => !empty($cliente->beneficiario) ? $cliente->beneficiario_id : null,
                'dependente_beneficiario_id' => !empty($cliente->dependenteBeneficiario) ? $cliente->dependente_beneficiario_id : null,
                'codigo_login_beneficiario' => !empty($user_beneficiario) ? $user_beneficiario->codigo_login : null,
                'codigo_login_dependente_beneficiario' => !empty($user_dependente_beneficiario) ? $user_dependente_beneficiario->codigo_login : null,
                'empresa_id' => $empresa_id,
                'empresa_nome' => $empresa_nome,
            ];

            DB::commit();
            return $this->sendResponse($data, '', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getTrace());
        }
    }


    public function autoRegistarFaseTres(Request $request)
    {
        $provincias = ['Maputo Cidade', 'Maputo Provincia', 'Gaza', 'Inhambane', 'Sofala', 'Manica', 'Zambezia', 'Tete', 'Nampula', 'Cabo Delgado', 'Niassa'];
        $request->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'nome' => 'nullable|string|max:255',
            // 'numero_identificacao' => 'nullable|string|max:50',
            'peso' => 'nullable|numeric',
            'altura' => 'nullable|numeric',
            'e_benefiairio_plano_saude' => 'required|boolean',
            'beneficiario_id' => 'nullable|required_if:e_benefiairio_plano_saude,1|integer|exists:beneficiarios,id',
            'tem_doenca_cronica' => 'required|boolean',
            'doenca_cronica_nome' => 'nullable|string|required_if:tem_doenca_cronica,1',
            'tipo_sanguineo' => ['nullable', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'provincia' => ['required', 'string', Rule::in($provincias)],
            'cidade' => 'required|string|max:100',
        ]);

        $input = $request->only(['nome', 'peso', 'altura', 'e_benefiairio_plano_saude', 'beneficiario_id', 'tem_doenca_cronica', 'tipo_sanguineo', 'provincia', 'cidade']);
        // $input = $request->only(['nome', 'numero_identificacao', 'peso', 'altura', 'e_benefiairio_plano_saude', 'beneficiario_id', 'tem_doenca_cronica', 'tipo_sanguineo', 'provincia', 'cidade']);
        $input['doenca_cronica_nome'] = explode(",", $request->doenca_cronica_nome);

        $cliente = $this->cliente->find($request->cliente_id);

        // dd($input);
        /* $doencas_pre = $request->doenca_cronica_nome;
        $doencas = rtrim($doencas_pre, "]");
        $doencas = ltrim($doencas_pre, "[");
        $doencas = \explode(",", $doencas_pre);
        dd($doencas); */

        if (empty($cliente))
            return $this->sendError('Cliente não encontrado!', 404);

        if (!empty($cliente_beneficiario))
            return $this->sendError('O Beneficiário informado já encontra-se associado a algum cliente!', 404);

        DB::beginTransaction();
        try {
            $cliente->fill($input);
            $cliente->save();

            $data = [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
            ];

            DB::commit();
            return $this->sendResponse($data, '', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }



    public function uploadFotoPerfil(Request $request)
    {
        $cliente = Auth::user();
        $request->validate(['foto_perfil' => 'nullable|mimes:jpeg,jpg,png']);
        $ficheiro = null;

        if (isset($request->foto_perfil) && isset($request->foto_documento)) {
            return $this->sendError('Informe um ficheiro por vez!', 400);
        } elseif (isset($request->foto_perfil)) {
            $ficheiro = 'foto_perfil';
        } elseif (isset($request->foto_documento)) {
            $ficheiro = 'foto_documento';
        } else {
            return $this->sendError('Não foi informado nenhum ficheiro!', 400);
        }


        if ($request->hasFile("$ficheiro") && $request->{$ficheiro}->isValid()) {
            // $path = 'clientes/' . $cliente->id . '/';
            $path = stogare_path_clientes() . "$cliente->id/";
            DB::beginTransaction();
            try {
                $upload = upload_file_s3($path, $request->{$ficheiro});
                if (!empty($upload)) {

                    $cliente->{$ficheiro} = $upload;
                    $cliente->save();

                    $data = [
                        "$ficheiro" => $cliente->{$ficheiro},
                    ];

                    DB::commit();
                    return $this->sendResponse($data, '', 200);
                }
            } catch (\Exception $e) {
                DB::rollback();
                return $this->sendError($e->getMessage());
            }
        } else {
            return $this->sendError('Arquivo informado inválido!', 400);
        }
    }

    public function autoRegistar(Request $request)
    {
        $provincias = ['Maputo Cidade', 'Maputo Provincia', 'Gaza', 'Inhambane', 'Sofala', 'Manica', 'Zambezia', 'Tete', 'Nampula', 'Cabo Delgado', 'Niassa'];
        $request->validate([
            'nome' => 'required|string|max:255',
            'peso' => 'nullable|numeric',
            'e_benefiairio_plano_saude' => 'required|boolean',
            'beneficiario_id' => 'nullable|required_if:e_benefiairio_plano_saude,1|integer|exists:beneficiarios,id',
            'tem_doenca_cronica' => 'required|boolean',
            'doenca_cronica_nome' => 'nullable|array|required_if:tem_doenca_cronica,1',
            'tipo_sanguineo' => ['nullable', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'provincia' => ['required', 'string', Rule::in($provincias)],
            'cidade' => 'required|string|max:100',
            'email' => 'required|email|unique:clientes,email',
            'password' => 'required|string|min:7',
        ]);


        $input = $request->all();
        $password_plana = $request->password;
        $input['beneficiario_id'] = $request->e_benefiairio_plano_saude ? $request->beneficiario_id : null;
        $input['email'] = strtolower($request->email);
        $input['password'] = bcrypt($password_plana);
        $input['activo'] = true;
        $input['logado_uma_vez'] = true;

        DB::beginTransaction();
        try {
            $cliente = $this->cliente->create($input);

            if (!$token = JWTAuth::attempt(['email' => strtolower($request->email), 'password' => $password_plana])) {
                DB::rollback();
                return $this->sendError('Não foi possível criar o usuário!', 400);
            }

            $data = [
                'id' => $cliente->id,
                'nome' => $cliente->nome,
            ];

            DB::commit();
            return response()->json(['token' => $token, 'expires_in' => $this->guard()->factory()->getTTL(), 'cliente' => $data], 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    public function recuperarSenha(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $cliente = $this->cliente->where('email', 'LIKE', strtolower($request->email))->first();
        $password_plana = "P@ss" . uniqid();

        if (empty($cliente)) {
            return $this->sendError('Usuário não encontrado!', 404);
        }

        DB::beginTransaction();
        try {
            $cliente->update(['password' => Hash::make($password_plana), 'logado_uma_vez' => false]);
            SendResetClientePasswordJob::dispatch($cliente, $password_plana)->delay(now()->addSeconds(10));
            DB::commit();
            return $this->sendResponse('', 'Nova senha enviada por email');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    public function trocarSenha(Request $request)
    {
        $request->validate([
            'forcado' => 'required|boolean',
            'password_nova' => 'required|string|min:7',
            'password_actual' => ['required_if:forcado,0', 'string'],

        ]);

        DB::beginTransaction();
        try {
            $cliente = Auth::user();

            if (!$cliente) {
                DB::rollback();
                return response()->json(['error' => 'Usuário não encontrado!'], 404);
            }

            if (!$request->forcado) {
                if (!Hash::check($request->password_actual, $cliente->password)) {
                    DB::rollback();
                    return $this->sendError('Senha actual inválida!', 400);
                }
            }



            $password = Hash::make($request->password_nova);
            $cliente->update(['password' => $password, 'activo' => true, 'logado_uma_vez' => true]);
            DB::commit();
            return response()->json(['message' => 'Senha alterada com sucesso!'], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function verificarBeneficiario(Request $request)
    {
        $request->validate(['codigo_login' => 'required|string', 'password' => 'required|string']);
        $codigo_login = $request['codigo_login'];
        $password = $request['password'];
        $beneficiario_id = null;
        $dependente_beneficiario_id = null;
        $guard = Auth::guard('api');
        // dd($guard);


        try {
            if (!$token_verificacao = $guard->attempt(['codigo_login' => $codigo_login, 'password' => $password])) {
                return $this->sendError('Não houve nenhuma correspondência para as credenciais informadas', 404);
            }

            $user = $guard->user();


            if (Str::startsWith(Str::upper($codigo_login), Str::upper('BENE'))) {

                $beneficiario = $user->beneficiario;
                if (empty($beneficiario) || $beneficiario->activo == false)
                    return $this->sendError('Beneficiário não encontrado ou inactivo!', 404);

                $beneficiario_id = $beneficiario->id;
            } else if (Str::startsWith(Str::upper($codigo_login), Str::upper('DEBENE'))) {

                $dependente_beneficiario = $user->dependenteBeneficiario;
                if (empty($dependente_beneficiario) || $dependente_beneficiario->activo == false)
                    return $this->sendError('Dependente não encontrado ou inactivo!', 404);

                $dependente_beneficiario_id = $dependente_beneficiario->id;
            } else {
                return $this->sendError('Código informado inválido!', 404);
            }

            $data = [
                'beneficiario_id' => $beneficiario_id,
                'dependente_beneficiario_id' => $dependente_beneficiario_id,
            ];

            return $this->sendResponse($data, '', 200);
        } catch (JWTException $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }


    public function associarBeneficiario(Request $request)
    {
        $request->validate(['codigo_login' => 'required|string', 'password' => 'required|string']);
        $credenciais = $request->only(['codigo_login', 'password']);
        $codigo_login = $request->codigo_login;
        $guard = Auth::guard('api');
        $data = [];
        // dd($guard);

        DB::beginTransaction();
        try {
            if (!$token_verificacao = $guard->attempt($credenciais)) {
                return $this->sendError('Não houve nenhuma correspondência para as credenciais informadas', 404);
            }

            $user = $guard->user();

            // dd($user);

            if (Str::startsWith(Str::upper($codigo_login), Str::upper('BENE'))) {

                $cliente = Auth::guard('cliente')->user();
                $cliente->load(['beneficiario', 'dependenteBeneficiario', 'beneficiario.empresa', 'dependenteBeneficiario.empresa']);
                if (!empty($cliente->dependenteBeneficiario))
                    return $this->sendError('A conta Cliente encontra-se associada à um Dependente!', 404);

                $user->load(['beneficiario']);
                $beneficiario = $user->beneficiario;
                if (empty($beneficiario))
                    return $this->sendError('Conta de Beneficiário inexistente!', 404);

                $cliente_verificacao = Cliente::where('beneficiario_id', $beneficiario->id)->first();
                if (!empty($cliente_verificacao))
                    return $this->sendError('A conta do Beneficiario informado já encontra-se associada a um outro Cliente ou à sua conta Cliente!', 404);

                $cliente->beneficiario()->associate($beneficiario);
                $cliente->save();

                $data = [
                    'empresa_id' => $cliente->beneficiario->empresa->id,
                    'empresa_nome' => $cliente->beneficiario->empresa->nome,
                ];
            } else if (Str::startsWith(Str::upper($codigo_login), Str::upper('DEBENE'))) {

                $cliente = Auth::guard('cliente')->user();
                $cliente->load(['beneficiario', 'dependenteBeneficiario']);
                if (!empty($cliente->beneficiario))
                    return $this->sendError('A conta Cliente encontra-se associada à um Beneficiário!', 404);

                $user->load(['dependenteBeneficiario']);
                $dependente_beneficiario = $user->dependenteBeneficiario;
                if (empty($dependente_beneficiario))
                    return $this->sendError('Conta de Dependente inexistente!', 404);

                $cliente_verificacao = Cliente::where('dependente_beneficiario_id', $dependente_beneficiario->id)->first();
                if (!empty($cliente_verificacao))
                    return $this->sendError('A conta do Dependente informado já encontra-se associada a um outro Cliente ou à sua conta Cliente!', 404);

                $cliente->dependenteBeneficiario()->associate($dependente_beneficiario);
                $cliente->save();

                $data = [
                    'empresa_id' => $cliente->dependenteBeneficiario->empresa->id,
                    'empresa_nome' => $cliente->dependenteBeneficiario->empresa->nome,
                ];
            } else {
                return $this->sendError('Código informado inválido!', 404);
            }


            DB::commit();
            return $this->sendResponse($data, 'Associado com sucesso!', 200);
        } catch (JWTException $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), 400);
        }
    }



    /* public function desassociarBeneficiario(Request $request)
    {
        $request->validate(['codigo_login' => 'required|string', 'password' => 'required|string']);
        $credenciais = $request->only(['codigo_login', 'password']);
        $guard = Auth::guard('api');
        // dd($guard);


        try {
            if (!$token_verificacao = $guard->attempt($credenciais)) {
                return $this->sendError('Não houve nenhuma correspondência para as credenciais informadas', 404);
            }

            $user = $guard->user();
            // dd($user);

            $beneficiario = Beneficiario::where('user_id', $user->id)->first();

            if (empty($beneficiario)) {
                return $this->sendError('Beneficiario não encontrado!', 404);
            }

            $cliente = Auth::guard('cliente')->user();
            if (empty($cliente->beneficiario_id))
                return $this->sendError('A sua conta não encontra-se associada à uma conta Beneficiario!', 404);

            if ($cliente->beneficiario_id != $beneficiario->id)
                return $this->sendError('A conta do Beneficiário informada não corresponde com a actual conta de Beneficiário associada à conta do Cliente!', 404);

            $cliente->beneficiario()->dissociate($beneficiario);
            $cliente->save();
            return $this->sendSuccess('Beneficiario desassociado com sucesso!', 200);
        } catch (JWTException $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    } */

    public function desassociarBeneficiario(Request $request)
    {
        $request->validate(['codigo_login' => 'required|string']);
        $codigo_login = $request->codigo_login;

        DB::beginTransaction();
        try {

            if (Str::startsWith(Str::upper($codigo_login), Str::upper('BENE'))) {

                $cliente = Auth::guard('cliente')->user();
                $cliente->load(['beneficiario:id,user_id', 'beneficiario.user:id,codigo_login']);
                if (empty($beneficiario = $cliente->beneficiario))
                    return $this->sendError('A sua conta Cliente não encontra-se associada à uma conta Beneficiario!', 404);

                if (!empty($user = $beneficiario->user)) {
                    if ($user->codigo_login != $codigo_login)
                        return $this->sendError('A conta do Beneficiário informada não corresponde com a actual conta de Beneficiário associada à conta do Cliente!', 404);
                } else {
                    return $this->sendError('Código de Usuário do Beneficiário informada não inválido!', 404);
                }

                $cliente->beneficiario()->dissociate($beneficiario);
                $cliente->save();
            } else if (Str::startsWith(Str::upper($codigo_login), Str::upper('DEBENE'))) {

                $cliente = Auth::guard('cliente')->user();
                $cliente->load(['dependenteBeneficiario:id,user_id', 'dependenteBeneficiario.user:id,codigo_login']);
                if (empty($dependente_deneficiario = $cliente->dependenteBeneficiario))
                    return $this->sendError('A sua conta Cliente não encontra-se associada à uma conta de Dependente!', 404);

                if (!empty($user = $dependente_deneficiario->user)) {
                    if ($user->codigo_login != $codigo_login)
                        return $this->sendError('A conta de Dependente informada não corresponde com a actual conta de Dependente associada à conta do Cliente!', 404);
                } else {
                    return $this->sendError('Código de Usuário do Dependente informado inválido!', 404);
                }

                $cliente->dependenteBeneficiario()->dissociate($dependente_deneficiario);
                $cliente->save();
            } else {
                return $this->sendError('Código informado inválido!', 404);
            }
            DB::commit();
            return $this->sendSuccess('Beneficiario desassociado com sucesso!', 200);
        } catch (JWTException $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), 400);
        }
    }



    public function me()
    {
        return $this->sendResponse($this->guard()->user(), 200);
    }

    public function logout()
    {
        try {
            $token = $this->guard()->logout();
        } catch (JWTException $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
