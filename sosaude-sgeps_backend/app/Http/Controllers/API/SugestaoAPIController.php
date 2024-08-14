<?php

namespace App\Http\Controllers\API;

use Response;
use App\Models\User;
use App\Models\Sugestao;
use Illuminate\Http\Request;
use App\Mail\SendSugestaoMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateSugestaoAPIRequest;
use App\Http\Requests\API\UpdateSugestaoAPIRequest;

/**
 * Class SugestaoController
 * @package App\Http\Controllers\API
 */

class SugestaoAPIController extends AppBaseController
{
    private $sugestao;
    private $user;
    /**
     * Create a new UtilizadorEmpresaAPIController instance.
     *
     * @return void
     */
    public function __construct(Sugestao $sugestao, User $user)
    {
        $this->sugestao = $sugestao;
        $this->user = $user;

        // Check if the current user has one of the roles, those are the codigo atribute and not id of the role
        // $this->middleware(["CheckRole:1"]);
    }

    /**
     * Display a listing of the Sugestao.
     * GET|HEAD /sugestaos
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        /* $sugestoes = $this->sugestao->with(
            [
                'user:id,nome,email,role_id,utilizador_farmacia_id,utilizador_clinica_id,utilizador_empresa_id,utilizador_administracao_id', 
                'user.role:id,role,seccao_id',
                'user.role.seccao:id,nome',
                'user.utilizadorFarmacia:id,contacto',
                'user.utilizadorClinica:id,contacto',
                'user.utilizadorFarmacia:id,contacto',
                'user.utilizadorAdministracao:id,contacto',
            ]
            )->get(); */

            $sugestoes = $this->sugestao->with(
                [
                    'user:id,nome,email,role_id', 
                    'user.role:id,role,seccao_id',
                    'user.role.seccao:id,nome',

                    'cliente'
                ]
                )
                ->orderBy('created_at', 'DESC')
                ->get()
                ->map( function ($sugestao) {

                    $usuario = !empty($sugestao->user) ? $sugestao->user->nome : (!empty($sugestao->cliente) ? $sugestao->cliente->nome : '');
                    $seccao = !empty($sugestao->user->role->seccao) ? $sugestao->user->role->seccao->nome : 'Cliente';
                    $contacto = $sugestao->contacto;
                    $email = !empty($sugestao->user) ? $sugestao->user->email : (!empty($sugestao->cliente) ? $sugestao->cliente->email : '');

                    return [
                        'id' => $sugestao->id,
                        'nome' => $usuario,
                        'seccao' => $seccao,
                        'contacto' => $contacto,
                        'email' => $email,
                        'conteudo' => $sugestao->conteudo,
                        'data' => date('d-m-Y', strtotime($sugestao->created_at))
                    ];
                });

                $data = [
                    'sugestoes' => $sugestoes
                ];

        return $this->sendResponse($data, 'Sugestões retrieved successfully');
    }

    /**
     * Store a newly created Sugestao in storage.
     * POST /sugestaos
     *
     * @param CreateSugestaoAPIRequest $request
     *
     * @return Response
     */
    public function store(CreateSugestaoAPIRequest $request)
    {
        $user = Auth::user();
        if (empty($user)) {
            return $this->sendError('User does not exists');
        }

        $emails_admins = User::admins()->pluck('email');

        $input = [
            'conteudo' => $request->conteudo,
            'user_id' => $user->id,
            'created_at' => now(),
        ];

        DB::beginTransaction();
        try {
            /** @var Sugestao $sugestao */
            $sugestao = Sugestao::create($input);
            $when = now()->addSeconds(10);

            foreach ($emails_admins as $key => $email) {
                Mail::to($email)->later($when, new SendSugestaoMail($request->conteudo));
            }
            DB::commit();
            return $this->sendResponse($sugestao->toArray(), 'Sugestão saved successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Display the specified Sugestao.
     * GET|HEAD /sugestaos/{id}
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        /** @var Sugestao $sugestao */
        $sugestao = Sugestao::find($id);

        if (empty($sugestao)) {
            return $this->sendError('Sugestao not found');
        }

        return $this->sendResponse($sugestao->toArray(), 'Sugestao retrieved successfully');
    }

    /**
     * Update the specified Sugestao in storage.
     * PUT/PATCH /sugestaos/{id}
     *
     * @param int $id
     * @param UpdateSugestaoAPIRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSugestaoAPIRequest $request)
    {
        /** @var Sugestao $sugestao */
        $sugestao = Sugestao::find($id);

        if (empty($sugestao)) {
            return $this->sendError('Sugestao not found');
        }

        $sugestao->fill($request->all());
        $sugestao->save();

        return $this->sendResponse($sugestao->toArray(), 'Sugestao updated successfully');
    }

    /**
     * Remove the specified Sugestao from storage.
     * DELETE /sugestaos/{id}
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        /** @var Sugestao $sugestao */
        $sugestao = Sugestao::find($id);

        if (empty($sugestao)) {
            return $this->sendError('Sugestao not found');
        }

        DB::beginTransaction();
        try {
            $sugestao->delete();
            DB::commit();
            return $this->sendSuccess('Sugestao deleted successfully');
        } catch (Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage());
        }
    }
}
