<?php

namespace App\Http\Controllers\API;

use Response;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Administracao;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\UtilizadorAdministracao;
use App\Http\Controllers\AppBaseController;
use App\Mail\SendNewPasswordUtilizadorMail;
use App\Mail\SendNewRegisteredUtilizadorBroadcastMail;
use App\Http\Requests\API\CreateUtilizadorAdministracaoAPIRequest;
use App\Http\Requests\API\UpdateUtilizadorAdministracaoAPIRequest;
use App\Http\Requests\CreateUpdateUtilizadorAdministracaoFormRequest;

/**
 * Class UtilizadorAdministracaoController
 * @package App\Http\Controllers\API
 */

class UtilizadorAdministracaoAPIController extends AppBaseController
{
    private $utilizador_empresa;
    private $user;
    /**
     * Create a new UtilizadorEmpresaAPIController instance.
     *
     * @return void
     */
    public function __construct(UtilizadorAdministracao $utilizador_admin, User $user)
    {
        $this->utilizador_admin = $utilizador_admin;
        $this->user = $user;

        // Check if the current user has one of the roles, those are the codigo atribute and not id of the role
        $this->middleware(["CheckRole:1"]);
    }

    /**
     * Display a listing of the UtilizadorAdministracao.
     * GET|HEAD /utilizadorAdministracaos
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {

        $utilizadores_admin = $this->utilizador_admin->all();

        return $this->sendResponse($utilizadores_admin->toArray(), 'Utilizador Administração retrieved successfully');
    }

    /**
     * Retrieve a listing of resources used to create the UtilizadorAdministracao.
     * GET|HEAD /utilizador_admin/create
     *
     * @return Response
     */
    public function create()
    {
        $roles = Role::select('id', 'role')->whereHas('seccao', function ($q) {
            $q->where('codigo', 1);
        })->get();

        $data = ['roles' => $roles];

        return $this->sendResponse($data, 'Resources retrieved successfully');
    }

    /**
     * Store a newly created UtilizadorAdministracao in storage.
     * POST /utilizadorAdministracaos
     *
     * @param CreateUtilizadorAdministracaoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateUpdateUtilizadorAdministracaoFormRequest $request)
    {
        $input = $request->all();

        $administracao = Administracao::first();

        if (empty($administracao)) {
            return $this->sendError('Administração não encontrada.');
        }

        DB::beginTransaction();
        try {

            /** @var User $user */
            $user = new User();
            $user->nome = $request->nome;
            $user->email = $request->email;
            $user->password = bcrypt('1234567'); // Default password, is changed after the created Event of Beneficiario
            $user->active = $request->activo;
            $user->loged_once = 0;
            $user->login_attempts = 0;
            $user->role_id = $request->role_id;
            $user->save();
            $input['user_id'] = $user->id;
            $input['administracao_id'] = $administracao->id;

            // $login_identifier = ['campo' => 'Email', 'valor' => 'osoriocassiano@gmail.com'];
            // return new SendNewPasswordUtilizadorMail($user, $login_identifier, 1234567);

            /** @var UtilizadorAdministracao $utilizador_administracao */
            $utilizador_admin = UtilizadorAdministracao::create($input);

            DB::commit();
            return $this->sendResponse($utilizador_admin->toArray(), 'Utilizador Administracao saved successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified UtilizadorAdministracao.
     * GET|HEAD /utilizador_admin/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var UtilizadorAdministracao $utilizador_admin */
        $utilizador_admin = UtilizadorAdministracao::find($id);

        if (empty($utilizador_admin)) {
            return $this->sendError('Utilizador Administração not found');
        }

        return $this->sendResponse($utilizador_admin->toArray(), 'Utilizador Administração retrieved successfully');
    }

    /**
     * Update the specified UtilizadorAdministracao in storage.
     * PUT/PATCH /utilizador_admin/{id}
     *
     * @param int $id
     * @param UpdateUtilizadorAdministracaoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, CreateUpdateUtilizadorAdministracaoFormRequest $request)
    {
        $utilizador_admin = UtilizadorAdministracao::find($id);

        if (empty($utilizador_admin)) {
            return $this->sendError('Utilizador Administração não encontrado.');
        }

        $input = $request->only(['nome', 'contacto', 'email', 'activo', 'role_id']);

        DB::beginTransaction();
        try {
            $utilizador_admin->update($input);
            /** @var User $user belongs to UtilizadorAdministracao being updated*/
            $user = $this->user->find($utilizador_admin->user_id);

            if (empty($user)) {
                DB::rollback();
                return $this->sendError('User of Utilizador Administração não encontrado.');
            }

            $user->update([
                'nome' => $utilizador_admin->nome,
                'email' => $utilizador_admin->email,
                'active' => $utilizador_admin->activo,
                'role_id' => $utilizador_admin->role_id,
            ]);

            DB::commit();
            return $this->sendResponse($utilizador_admin->toArray(), 'Utilizador Administração actualizado com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Remove the specified UtilizadorAdministracao from storage.
     * DELETE /utilizadorAdministracaos/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $utilizador_admin = UtilizadorAdministracao::find($id);
        if (empty($utilizador_admin)) {
            return $this->sendError('Utilizador Administraçãoo não encontrado.', 404);
        }

        $user = $this->user->find($utilizador_admin->user_id);
        if (empty($user)) {
            DB::rollback();
            return $this->sendError('Usuário do Utilizador Administração não encontrado.', 404);
        }

        DB::beginTransaction();
        try {
            $utilizador_admin->delete();
            $user->delete();
            DB::commit();
            return $this->sendSuccess('Utilizador Administração removido com sucesso.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    public function testMail()
    {
        $user = Auth::user();
        // dd($user);
        $login_identifier = ['campo' => 'Email', 'valor' => $user->email];
        // return (new SendNewPasswordUtilizadorMail($user, $login_identifier, '1234567'))->render();
        $users = User::all();
        // dd($users);
        // $main_mail = ['osoriocassiano@hotmail.com'];
        $emails = ['osoriocassiano@gmail.com', 'osorio.malache@marrar.co.mz', 'osoriocassiano@hotmail.com'];
        // return (new SendNewRegisteredUtilizadorBroadcastMail($user))->render();
        // Mail::to($main_mail)->bcc($emails)->send(new SendNewRegisteredUtilizadorBroadcastMail($user));
        $when = now()->addSeconds(10);
        foreach($emails as $mail) {
            Mail::to($mail)->later($when, new SendNewRegisteredUtilizadorBroadcastMail($user));
        }
    }
}
