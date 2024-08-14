<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNewOrganizationMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $categoria;
    protected $organizacao;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($categoria, $organizacao)
    {
        $this->categoria = $categoria;
        $this->organizacao = $organizacao;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.organization.send_new_organization')
        ->subject('Registo de uma nova organização na plataforma SGEPS')
        ->with([
            'categoria' => $this->categoria,
            'organizacao' => $this->organizacao,
        ]);
    }
}
