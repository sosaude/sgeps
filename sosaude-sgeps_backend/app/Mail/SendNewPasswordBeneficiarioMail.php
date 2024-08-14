<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendNewPasswordBeneficiarioMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $user;
    protected $login_identifier;
    protected $plain_password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, array $login_identifier, $plain_password)
    {
        $this->user = $user;
        $this->login_identifier = $login_identifier;
        $this->plain_password = $plain_password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('Credenciais')
        ->markdown('mails.credentials.send_new_password_beneficiario')
        ->with([
            'nome' => $this->user->nome,
            'identificador_login_campo' => $this->login_identifier['campo'],
            'identificador_login_valor' => $this->login_identifier['valor'],
            'plain_password' => $this->plain_password,
            'perfil' => !empty($this->user->role) ? $this->user->role->role : '',
        ]);
    }
}
