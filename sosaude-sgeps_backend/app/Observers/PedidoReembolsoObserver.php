<?php

namespace App\Observers;

use App\Models\PedidoReembolso;
use App\Models\UtilizadorEmpresa;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendPedidoReembolsoMail;

class PedidoReembolsoObserver
{
    /**
     * Handle the pedido reembolso "created" event.
     *
     * @param  \App\PedidoReembolso  $pedidoReembolso
     * @return void
     */
    public function created(PedidoReembolso $pedido_reembolso)
    {
        $pedido_reembolso->load(['empresa:id,nome,email', 'beneficiario:id,nome,email', 'estadoPedidoReembolso:id,codigo,nome']);
        $estado_pedido_reembolso = $pedido_reembolso->estadoPedidoReembolso->codigo;

        $data = [
            'membro_principal' => !empty($pedido_reembolso->beneficiario) ? $pedido_reembolso->beneficiario->nome : '',
            'id_processo' => $pedido_reembolso->id,
            'estado_pedido_reembolso' => !empty($pedido_reembolso->estadoPedidoReembolso) ? $pedido_reembolso->estadoPedidoReembolso->nome : ''
        ];

        $when = now()->addSeconds(10);

        if($estado_pedido_reembolso == 10) {
            // Aguardando Confirmação => Empresa
            $this->sendToEmpresa($pedido_reembolso, $data, $when);
        } else if($estado_pedido_reembolso == 11) {
            // Aguardando Pagamento => Beneficiario, Empresa
            $this->sendToBeneficiario($pedido_reembolso, $data, $when);
            $this->sendToEmpresa($pedido_reembolso, $data, $when);
        } else if($estado_pedido_reembolso == 12) {
            // Pagamento Processado => Beneficiario
            $this->sendToBeneficiario($pedido_reembolso, $data, $when);
        } else if($estado_pedido_reembolso == 13) {
            // Aguardando Correcção => Beneficiario
            $this->sendToBeneficiario($pedido_reembolso, $data, $when);
        }
    }

    /**
     * Handle the pedido reembolso "updated" event.
     *
     * @param  \App\PedidoReembolso  $pedidoReembolso
     * @return void
     */
    public function updated(PedidoReembolso $pedido_reembolso)
    {
        $pedido_reembolso->load(['empresa:id,nome,email', 'beneficiario:id,nome,email', 'estadoPedidoReembolso:id,codigo,nome']);
        $estado_pedido_reembolso = $pedido_reembolso->estadoPedidoReembolso->codigo;

        $data = [
            'membro_principal' => !empty($pedido_reembolso->beneficiario) ? $pedido_reembolso->beneficiario->nome : '',
            'id_processo' => $pedido_reembolso->id,
            'estado_pedido_reembolso' => !empty($pedido_reembolso->estadoPedidoReembolso) ? $pedido_reembolso->estadoPedidoReembolso->nome : ''
        ];

        $when = now()->addSeconds(10);

        if($estado_pedido_reembolso == 10) {
            // Aguardando Confirmação => Empresa
            $this->sendToEmpresa($pedido_reembolso, $data, $when);
        } else if($estado_pedido_reembolso == 11) {
            // Aguardando Pagamento => Beneficiario, Empresa
            $this->sendToBeneficiario($pedido_reembolso, $data, $when);
            $this->sendToEmpresa($pedido_reembolso, $data, $when);
        } else if($estado_pedido_reembolso == 12) {
            // Pagamento Processado => Beneficiario
            $this->sendToBeneficiario($pedido_reembolso, $data, $when);
        } else if($estado_pedido_reembolso == 13) {
            // Aguardando Correcção => Beneficiario
            $this->sendToBeneficiario($pedido_reembolso, $data, $when);
        }
    }

    /**
     * Handle the pedido reembolso "deleted" event.
     *
     * @param  \App\PedidoReembolso  $pedidoReembolso
     * @return void
     */
    public function deleted(PedidoReembolso $pedidoReembolso)
    {
        //
    }

    /**
     * Handle the pedido reembolso "restored" event.
     *
     * @param  \App\PedidoReembolso  $pedidoReembolso
     * @return void
     */
    public function restored(PedidoReembolso $pedidoReembolso)
    {
        //
    }

    /**
     * Handle the pedido reembolso "force deleted" event.
     *
     * @param  \App\PedidoReembolso  $pedidoReembolso
     * @return void
     */
    public function forceDeleted(PedidoReembolso $pedidoReembolso)
    {
        //
    }


    protected function sendToEmpresa($pedido_reembolso, $data, $when)
    {
        $emails_utilizadores_empresa = UtilizadorEmpresa::emails($pedido_reembolso->empresa_id)
            ->get()
            ->filter(function ($utilizador_empresa) {
                return filter_var($utilizador_empresa->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        if (!empty($empresa_email = $pedido_reembolso->empresa->email)) {
            if (filter_var($empresa_email, FILTER_VALIDATE_EMAIL) != false)
                Mail::to($empresa_email)->later($when, new SendPedidoReembolsoMail($data));
        };

        foreach ($emails_utilizadores_empresa as $key => $email) {
            Mail::to($email)->later($when, new SendPedidoReembolsoMail($data));
        }
    }

    protected function sendToBeneficiario($pedido_reembolso, $data, $when)
    {
        if (!empty($beneficiario_email = $pedido_reembolso->beneficiario->email)) {

            if (filter_var($beneficiario_email, FILTER_VALIDATE_EMAIL) != false) {
                Mail::to($beneficiario_email)->later($when, new SendPedidoReembolsoMail($data));
            }
        };
    }
}
