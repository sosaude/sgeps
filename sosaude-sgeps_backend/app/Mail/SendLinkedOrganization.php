<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendLinkedOrganization extends Mailable
{
    use Queueable, SerializesModels;
    protected $farmacias;
    protected $unidades_sanitarias;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($farmacias, $unidades_sanitarias)
    {
        $this->farmacias = $farmacias;
        $this->unidades_sanitarias = $unidades_sanitarias;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.organization.send_linked_organization')
        ->subject('Organizações associadas a Empresa')
        ->with([
            'farmacias' => $this->farmacias,
            'unidades_sanitarias' => $this->unidades_sanitarias,
        ]);
    }
}
