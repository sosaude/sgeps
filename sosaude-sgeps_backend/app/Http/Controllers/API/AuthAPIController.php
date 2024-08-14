<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Mail\SendDisabledLoginMail;
use App\Http\Controllers\Controller;
use App\Models\UtilizadorEmpresa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthAPIController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    private $teste;
    public function __construct(UtilizadorEmpresa $teste)
    {
        $this->teste = $teste;
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $identifier = $request->identifier;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'codigo_login';
        $request->merge([$field => $identifier]);

        $credentials = $request->only($field, 'password');

        try {

            if (!$token = JWTAuth::attempt($credentials)) {
                $user = User::withTrashed()->where($field, $identifier)->first();
                if (isset($user)) {
                    $user->login_attempts++;
                    if ($user->login_attempts >= 3) {
                        $user->disbled_login_by_wrong_pass = 1;
                        $user->login_attempts = 0;

                        if (!$user->sent_disabled_login) {
                            if (!empty($to = $this->utilizadorEmail($user))) {
                                $when = now()->addSeconds(10);
                                Mail::to($to)->later($when, new SendDisabledLoginMail($user));
                                $user->sent_disabled_login = true;
                            }
                        }
                    }
                    $user->save();
                }
                return response()->json(['error' => 'Invalid_credentials or user is disactivated'], 400);
            }



            $user = Auth::user();
            $user->login_attempts = 0;
            $user->save();
            $utilizadorEntidade = $user->utilizadorEntidade();
            $utilizadorEntidade->email_verificado = true;
            $utilizadorEntidade->save();
            // dd($utilizadorEntidade);



            $organizacao_nome = $this->getOrganizacaoNome($user);
            $user->load('permissaos:nome,codigo');
            $user->load('role.seccao');

            $data = [
                'id' => $user->id,
                'nome' => $user->nome,
                'active' => $user->active,
                'disbled_login_by_wrong_pass' => $user->disbled_login_by_wrong_pass,
                'loged_once' => $user->loged_once,
                'organizacao_nome' => $organizacao_nome,
                'role' => $user->role,
                'permissaos' => $user->permissaos,
            ];

            if ($user->disbled_login_by_wrong_pass == 1 || $user->active == 0) {
                $request->merge(['token' => $token]);
                JWTAuth::parseToken()->invalidate();
                return response()->json(['error' => 'User is Inactive', 'token' => null], 401);
            }

            return response()->json(['token' => $token, 'expires_in' => $this->guard()->factory()->getTTL(), 'user' => $data], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getTrace()], 400);
        }
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        /* $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']); */

        // Get JWT Token from the request header key "Authorization"
        $token = $request->header('Authorization');
        // Invalidate the token
        try {
            JWTAuth::parseToken()->invalidate($token);
            return response()->json([
                'status' => 'success',
                'message' => 'User successfully logget out.',
            ]);
        } catch (TokenExpiredException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired.',

            ], 401);
        } catch (TokenInvalidException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Token.',
            ], 401);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            response()->json([
                'status' => 'error',
                'message' => 'Failed to logout, please try again.',
            ], 500);
        }
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'user' => $user->toArray(),
        ]);
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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'nullable|unique:users,email',
            'codigo_login' => 'nullable|unique:users,codigo_login',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        try {
            $user = new User();
            $user->nome = $request->name;
            $user->email = $request->email;
            $user->role_id = 1;
            $user->password = bcrypt($request->password);
            $user->save();
            $token = JWTAuth::fromUser($user);
            return response()->json(compact('user', 'token', 201));
        } catch (Exception $e) {
            response()->json(['error' => $e->getMessage() . $e->getLine()], 404);
        }
    }

    protected function getOrganizacaoNome($user)
    {
        // Grab the User Company
        if ($user->role->seccao->code == 1) {

            if ($user->utilizadorAdministracao->administracao) {

                $organizacao_nome = $user->utilizadorAdministracao->administracao->nome;

                $user->unsetRelation('utilizadorAdministracao');

                return $organizacao_nome;
            }
        } elseif ($user->role->seccao->code == 2) {

            if ($user->utilizadorEmpresa) {

                $organizacao_nome = $user->utilizadorEmpresa->empresa->nome;

                $user->unsetRelation('utilizadorEmpresa');

                return $organizacao_nome;
            } elseif ($user->beneficiario) {

                $organizacao_nome = $user->beneficiario->empresa->nome;

                $user->unsetRelation('beneficiario');

                return $organizacao_nome;
            } elseif ($user->dependenteBeneficiario) {

                $organizacao_nome = $user->dependenteBeneficiario->empresa->nome;

                $user->unsetRelation('dependenteBeneficiario');

                return $organizacao_nome;
            }
        } elseif ($user->role->seccao->code == 3) {

            if ($user->utilizadorFarmacia) {

                $organizacao_nome = $user->utilizadorFarmacia->farmacia->nome;

                $user->unsetRelation('utilizadorFarmacia');

                return $organizacao_nome;
            }
        } elseif ($user->role->seccao->code == 4) {

            if ($user->utilizadorClinica) {

                $organizacao_nome = $user->utilizadorClinica->clinica->nome;

                $user->unsetRelation('utilizadorClinica');

                return $organizacao_nome;
            }
        }

        return null;
    }

    protected function utilizadorEmail($user)
    {
        $user->load([
            'utilizadorAdministracao',
            'utilizadorEmpresa',
            'utilizadorFarmacia',
            'utilizadorUnidadeSanitaria',
            'beneficiario',
            'dependenteBeneficiario'
        ]);
        $email = null;

        if (!empty($user->utilizadorAdministracao)) {
            $email = $user->utilizadorAdministracao->email;
        } else if (!empty($utilizador_empresa = $user->utilizadorEmpresa)) {
            if (!empty($utilizador_empresa->email)) {
                $email = $utilizador_empresa->email;
            }
        } else if (!empty($utilizador_farmacia = $user->utilizadorFarmacia)) {
            if (!empty($utilizador_farmacia->email)) {
                $email = $utilizador_farmacia->email;
            }
        } else if (!empty($utilizador_unidade_sanitaria = $user->utilizadorUnidadeSanitaria)) {
            if (!empty($utilizador_unidade_sanitaria->email)) {
                $email = $utilizador_unidade_sanitaria->email;
            }
        } else if (!empty($utilizador_beneficiario = $user->beneficiario)) {
            if (!empty($utilizador_beneficiario->email)) {
                $email = $utilizador_beneficiario->email;
            }
        } else if (!empty($utilizador_dependente_beneficiario = $user->dependenteBeneficiario)) {
            if (!empty($utilizador_dependente_beneficiario->email)) {
                $email = $utilizador_dependente_beneficiario->email;
            }
        }

        return $email;
    }
}
