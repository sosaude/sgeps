<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPedidoReembolsoMail extends Mailable
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
        return $this->markdown('mails.organization.send_pedido_reembolso')
        ->subject('Actualização do Pedido de Reembolso')
        ->with([
            'membro_principal' => $this->data['membro_principal'],
            'id_processo' => $this->data['id_processo'],
            'estado_pedido_reembolso' => $this->data['estado_pedido_reembolso']
        ]);
    }
}
