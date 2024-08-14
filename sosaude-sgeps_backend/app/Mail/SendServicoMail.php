<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendServicoMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $servico;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($servico)
    {
        $this->servico = $servico;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.servico.send_servico')
            ->subject('Novo Serviço registado na plataforma SGEPS')
            ->with([
                'nome' => $this->servico->nome,
                'categoria' => !empty($this->servico->categoriaServico) ? $this->servico->categoriaServico->nome : '',
            ]);
    }
}
