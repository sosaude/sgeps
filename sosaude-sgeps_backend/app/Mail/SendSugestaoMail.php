<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendSugestaoMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $conteudo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($conteudo)
    {
        $this->conteudo = $conteudo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.organization.send_sugestao')
        ->subject('Submissão de Sugestão no SGEPS')
        ->with([
            'conteudo' => $this->conteudo
        ]);
    }
}
