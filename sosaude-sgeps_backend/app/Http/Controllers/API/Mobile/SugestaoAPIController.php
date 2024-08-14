<?php

namespace App\Http\Controllers\API\Mobile;

use Exception;
use App\Models\User;
use App\Models\Sugestao;
use Illuminate\Http\Request;
use App\Mail\SendSugestaoMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AppBaseController;

class SugestaoAPIController extends AppBaseController
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
    public function index()
    {
        $cliente = Auth::user();
        $sugestoes = $this->sugestao
            ->with('cliente:id,nome')
            ->where('cliente_id', $cliente->id)
            ->get(['id', 'conteudo', 'cliente_id', 'created_at'])
            ->map(function ($sugestao) {
                return [
                    'nome' => !empty($sugestao->cliente) ? $sugestao->cliente->nome : '',
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
        $request->validate(['conteudo' => 'required|string|max:255']);
        $input = $request->only(['conteudo']);

        $cliente = Auth::user();
        $input['cliente_id'] = $cliente->id;
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
            return $this->sendSuccess('Sugestão gravada com sucesso!', 200);
            // return $this->sendResponse('Sugestão gravada com sucesso!', 200);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
