<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNewRegisteredUtilizadorBroadcastMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->markdown('mails.credentials.send_new_registered_utilizador_broadcast')
        ->with([
            'nome' => $this->user->nome,
            'perfil' => !empty($this->user->role) ? $this->user->role->role : '',
        ]);
    }
}
