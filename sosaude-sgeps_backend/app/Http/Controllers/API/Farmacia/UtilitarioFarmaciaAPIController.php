<?php

namespace App\Http\Controllers\API\Farmacia;

use App\Models\User;
use App\Models\Sugestao;
use Illuminate\Http\Request;
use App\Mail\SendSugestaoMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AppBaseController;

class UtilitarioFarmaciaAPIController extends AppBaseController
{
    private $sugestao;

    public function __construct(Sugestao $sugestao)
    {
        $this->sugestao = $sugestao;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSugestao()
    {
        if (Gate::denies('gerir sugestão')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }

        $user = Auth::user();
        $sugestoes = $this->sugestao
            ->with('user:id,nome')
            ->where('user_id', $user->id)
            ->get(['id', 'conteudo', 'cliente_id', 'created_at'])
            ->map(function ($sugestao) {
                return [
                    'nome' => !empty($sugestao->user) ? $sugestao->user->nome : '',
                    'conteudo' => $sugestao->conteudo,
                    'data' => date('d-m-Y', strtotime($sugestao->created_at))
                ];
            });

        $data = [
            'sugestoes' => $sugestoes,
        ];

        return $this->sendResponse($data, '', 200);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeSugestao(Request $request)
    {
        if (Gate::denies('gerir sugestão')) {
            return $this->sendError('Esta acção não está autorizada!', 403);
        }
        //
        $request->validate(['conteudo' => 'required|string|max:255']);
        $input = $request->only(['conteudo']);

        $user = Auth::user();
        $input['user_id'] = $user->id;
        $emails_admins = User::admins()->pluck('email');
        $when = now()->addSeconds(10);
        // dd($input);

        DB::beginTransaction();
        try {
            $ugestao = $this->sugestao->create($input);            

            foreach ($emails_admins as $key => $email) {
                Mail::to($email)->later($when, new SendSugestaoMail($request->conteudo));
            }
            DB::commit();
            return $this->sendSuccess('Sugestão registada com sucesso!', 200);
            // return $this->sendResponse('Sugestão gravada com sucesso!', 200);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}
