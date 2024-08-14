<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendBaixaMember extends Mailable
{
    use Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.member.send_baixa_member')
        ->subject('Actualização do Gasto')
        ->with([
            'membro_principal' => $this->data['membro_principal'],
            'id_processo' => $this->data['id_processo'],
            'referencia_processo' => $this->data['referencia_processo'],
            'estado_baixa' => $this->data['estado_baixa'],
            'instituicao_proveniencia' => $this->data['instituicao_proveniencia'],
        ]);
    }
}
