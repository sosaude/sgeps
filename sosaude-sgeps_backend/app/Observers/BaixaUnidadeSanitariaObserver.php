<?php

namespace App\Observers;

use App\Mail\SendBaixaMember;
use App\Models\UtilizadorEmpresa;
use App\Mail\SendBaixaOrganization;
use Illuminate\Support\Facades\Mail;
use App\Models\BaixaUnidadeSanitaria;
use App\Models\UtilizadorUnidadeSanitaria;

class BaixaUnidadeSanitariaObserver
{
    /**
     * Handle the baixa unidade sanitaria "created" event.
     *
     * @param  \App\BaixaUnidadeSanitaria  $baixaUnidadeSanitaria
     * @return void
     */
    public function created(BaixaUnidadeSanitaria $baixa_unidade_sanitaria)
    {
        $baixa_unidade_sanitaria->load(['unidadeSanitaria:id,nome,email', 'empresa:id,nome,email', 'beneficiario:id,nome,email', 'estadoBaixa:id,codigo,referencia,nome']);
        $estdo_baixa_codigo = $baixa_unidade_sanitaria->estadoBaixa->codigo;

        $data = [
            'membro_principal' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : '',
            'id_processo' => $baixa_unidade_sanitaria->id,
            'referencia_processo' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->referencia : '',
            'estado_baixa' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->nome : '',
            'instituicao_proveniencia' => !empty($baixa_unidade_sanitaria->unidadeSanitaria) ? $baixa_unidade_sanitaria->unidadeSanitaria->nome : '',
        ];

        $when = now()->addSeconds(10);

        if ($estdo_baixa_codigo == 7) {
            // Pedido de Aprovacao Rejeitado => Beneficiario, Farmacia
            $this->sendToBeneficiario($baixa_unidade_sanitaria, $data, $when);
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 8) {
            // Pedido de Aprovacao Aguardando Aprovacao => Beneficiario, Empresa
            $this->sendToBeneficiario($baixa_unidade_sanitaria, $data, $when);
            $this->sendToEmpresa($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 9) {
            // Pedido de Aprovacao Aguardando Inicializacao do Gasto => Beneficiario, Farmacia
            $this->sendToBeneficiario($baixa_unidade_sanitaria, $data, $when);
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 10) {
            // Aguardando Confirmação => Beneficiario, Empresa
            // $this->sendToBeneficiario($baixa_unidade_sanitaria, $data, $when);
            $this->sendToEmpresa($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 11) {
            // Aguardando Pagamento => Farmacia, Empresa
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
            $this->sendToEmpresa($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 12) {
            // Pagamento Processado => Farmacia
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 13) {
            // Devolvido => Farmacia
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
        }
    }

    /**
     * Handle the baixa unidade sanitaria "updated" event.
     *
     * @param  \App\BaixaUnidadeSanitaria  $baixaUnidadeSanitaria
     * @return void
     */
    public function updated(BaixaUnidadeSanitaria $baixa_unidade_sanitaria)
    {
        $baixa_unidade_sanitaria->load(['unidadeSanitaria:id,nome,email', 'empresa:id,nome,email', 'beneficiario:id,nome,email', 'estadoBaixa:id,codigo,referencia,nome']);
        $estdo_baixa_codigo = $baixa_unidade_sanitaria->estadoBaixa->codigo;

        $data = [
            'membro_principal' => !empty($baixa_unidade_sanitaria->beneficiario) ? $baixa_unidade_sanitaria->beneficiario->nome : '',
            'id_processo' => $baixa_unidade_sanitaria->id,
            'referencia_processo' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->referencia : '',
            'estado_baixa' => !empty($baixa_unidade_sanitaria->estadoBaixa) ? $baixa_unidade_sanitaria->estadoBaixa->nome : '',
            'instituicao_proveniencia' => !empty($baixa_unidade_sanitaria->unidadeSanitaria) ? $baixa_unidade_sanitaria->unidadeSanitaria->nome : '',
        ];

        $when = now()->addSeconds(10);

        if ($estdo_baixa_codigo == 7) {
            // Pedido de Aprovacao Rejeitado => Beneficiario, Farmacia
            $this->sendToBeneficiario($baixa_unidade_sanitaria, $data, $when);
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 8) {
            // Pedido de Aprovacao Aguardando Aprovacao => Beneficiario, Empresa
            $this->sendToBeneficiario($baixa_unidade_sanitaria, $data, $when);
            $this->sendToEmpresa($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 9) {
            // Pedido de Aprovacao Aguardando Inicializacao do Gasto => Beneficiario, Farmacia
            $this->sendToBeneficiario($baixa_unidade_sanitaria, $data, $when);
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 10) {
            // Aguardando Confirmação => Beneficiario, Empresa
            $this->sendToBeneficiario($baixa_unidade_sanitaria, $data, $when);
            $this->sendToEmpresa($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 11) {
            // Aguardando Pagamento => Farmacia, Empresa
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
            $this->sendToEmpresa($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 12) {
            // Pagamento Processado => Farmacia
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
        } else if ($estdo_baixa_codigo == 13) {
            // Devolvido => Farmacia
            $this->sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when);
        }
    }

    /**
     * Handle the baixa unidade sanitaria "deleted" event.
     *
     * @param  \App\BaixaUnidadeSanitaria  $baixaUnidadeSanitaria
     * @return void
     */
    public function deleted(BaixaUnidadeSanitaria $baixaUnidadeSanitaria)
    {
        //
    }

    /**
     * Handle the baixa unidade sanitaria "restored" event.
     *
     * @param  \App\BaixaUnidadeSanitaria  $baixaUnidadeSanitaria
     * @return void
     */
    public function restored(BaixaUnidadeSanitaria $baixaUnidadeSanitaria)
    {
        //
    }

    /**
     * Handle the baixa unidade sanitaria "force deleted" event.
     *
     * @param  \App\BaixaUnidadeSanitaria  $baixaUnidadeSanitaria
     * @return void
     */
    public function forceDeleted(BaixaUnidadeSanitaria $baixaUnidadeSanitaria)
    {
        //
    }

    protected function sendToBeneficiario($baixa_unidade_sanitaria, $data, $when)
    {
        if (!empty($beneficiario = $baixa_unidade_sanitaria->beneficiario)) {

            if (filter_var($beneficiario->email, FILTER_VALIDATE_EMAIL) != false) {
                Mail::to($beneficiario->email)->later($when, new SendBaixaMember($data));
            }
        }
    }

    protected function sendToUnidadeSanitaria($baixa_unidade_sanitaria, $data, $when)
    {
        $emails_utilizadores_unidade_sanitaria = UtilizadorUnidadeSanitaria::where('unidade_sanitaria_id', $baixa_unidade_sanitaria->unidade_sanitaria_id)
            ->get()
            ->filter(function ($utilizador_unidade_sanitaria, $key) {
                return filter_var($utilizador_unidade_sanitaria->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();


        if (filter_var($baixa_unidade_sanitaria->unidadeSanitaria->email, FILTER_VALIDATE_EMAIL) != false) {
            Mail::to($baixa_unidade_sanitaria->unidadeSanitaria->email)->later($when, new SendBaixaOrganization($data));
        }

        foreach ($emails_utilizadores_unidade_sanitaria as $key => $email) {
            Mail::to($email)->later($when, new SendBaixaOrganization($data));
        }
    }

    protected function sendToEmpresa($baixa_unidade_sanitaria, $data, $when)
    {
        $emails_utilizadores_empresa = UtilizadorEmpresa::emails($baixa_unidade_sanitaria->empresa_id)
            ->get()
            ->filter(function ($utilizador_empresa, $key) {
                return filter_var($utilizador_empresa->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        if (!empty($empresa = $baixa_unidade_sanitaria->empresa)) {

            if (filter_var($empresa->email, FILTER_VALIDATE_EMAIL)  != false) {
                Mail::to($empresa->email)->later($when, new SendBaixaOrganization($data));
            }
        }

        foreach ($emails_utilizadores_empresa as $key => $email) {
            Mail::to($email)->later($when, new SendBaixaOrganization($data));
        }
    }
}
