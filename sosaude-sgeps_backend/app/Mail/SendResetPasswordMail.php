<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipient;
    public $user;
    public $hash;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recipient, User $user, $hash)
    {
        $this->recipient = $recipient;
        $this->user = $user;
        $this->hash = $hash;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.credentials.send_reset_password')->subject('Password reseted')->to($this->recipient);
    }
}
