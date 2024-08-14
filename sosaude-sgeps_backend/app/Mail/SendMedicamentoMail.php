<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMedicamentoMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $medicamento;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($medicamento)
    {
        $this->medicamento = $medicamento;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.medicamento.send_medicamento')
        ->subject('Novo Medicamento registado na plataforma SGEPS')
        ->with([
            'nome_generico' => !empty($this->medicamento->nomeGenerico) ? $this->medicamento->nomeGenerico->nome : '',
            'forma' => !empty($this->medicamento->formaMedicamento) ? $this->medicamento->formaMedicamento->forma : '',
            'dosagem' => $this->medicamento->dosagem,
            'grupo' => !empty($this->medicamento->grupoMedicamento) ? $this->medicamento->grupoMedicamento->nome : '',
            'subgrupo' => !empty($this->medicamento->subGrupoMedicamento) ? $this->medicamento->subGrupoMedicamento->nome : '',
            'subclasse' => !empty($this->medicamento->subClasseMedicamento) ? $this->medicamento->subClasseMedicamento->nome : '',
        ]);
    }
}
