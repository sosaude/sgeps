<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNewUtilizadorMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $utilizador_nome;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($utilizador_nome)
    {
        $this->utilizador_nome = $utilizador_nome;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.utilizador.send_new_utilizador')
        ->subject('Novo Colaborador Registado')
        ->with([
            'nome' => $this->utilizador_nome
        ]);
    }
}
