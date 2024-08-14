<?php

namespace App\Observers;

use App\Mail\SendBaixaMember;
use App\Models\BaixaFarmacia;
use App\Models\UtilizadorEmpresa;
use App\Mail\SendBaixaOrganization;
use App\Models\UtilizadorFarmacia;
use App\Models\UtilizadorUnidadeSanitaria;
use Illuminate\Support\Facades\Mail;

class BaixaFarmaciaObserver
{
    /**
     * Handle the baixa farmacia "created" event.
     *
     * @param  \App\BaixaFarmacia  $baixaFarmacia
     * @return void
     */
    public function created(BaixaFarmacia $baixa_farmacia)
    {
        $baixa_farmacia->load(['farmacia:id,nome,email', 'empresa:id,nome,email', 'beneficiario:id,nome,email', 'estadoBaixa:id,codigo,referencia,nome']);
        $estdo_baixa_codigo = $baixa_farmacia->estadoBaixa->codigo;

        $data = [
            'membro_principal' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : '',
            'id_processo' => $baixa_farmacia->id,
            'referencia_processo' => !empty($baixa_farmacia->estadoBaixa) ? $baixa_farmacia->estadoBaixa->referencia : '',
            'estado_baixa' => !empty($baixa_farmacia->estadoBaixa) ? $baixa_farmacia->estadoBaixa->nome : '',
            'instituicao_proveniencia' => !empty($baixa_farmacia->farmacia) ? $baixa_farmacia->farmacia->nome : '',
        ];

        $when = now()->addSeconds(10);

        if ($estdo_baixa_codigo == 7) {
            // Pedido de Aprovacao Rejeitado => Beneficiario, Farmacia
            $this->sendToBeneficiario($baixa_farmacia, $data, $when);
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 8) {
            // Pedido de Aprovacao Aguardando Aprovacao => Beneficiario, Empresa
            $this->sendToBeneficiario($baixa_farmacia, $data, $when);
            $this->sendToEmpresa($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 9) {
            // Pedido de Aprovacao Aguardando Inicializacao do Gasto => Beneficiario, Farmacia
            $this->sendToBeneficiario($baixa_farmacia, $data, $when);
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 10) {
            // Aguardando Confirmação => Beneficiario, Empresa
            // $this->sendToBeneficiario($baixa_farmacia, $data, $when);
            $this->sendToEmpresa($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 11) {
            // Aguardando Pagamento => Farmacia, Empresa
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
            $this->sendToEmpresa($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 12) {
            // Pagamento Processado => Farmacia
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 13) {
            // Devolvido => Farmacia
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
        }

    }

    /**
     * Handle the baixa farmacia "updated" event.
     *
     * @param  \App\BaixaFarmacia  $baixaFarmacia
     * @return void
     */
    public function updated(BaixaFarmacia $baixa_farmacia)
    {
        $baixa_farmacia->load(['farmacia:id,nome,email', 'empresa:id,nome,email', 'beneficiario:id,nome,email', 'estadoBaixa:id,codigo,referencia,nome']);
        $estdo_baixa_codigo = $baixa_farmacia->estadoBaixa->codigo;

        $data = [
            'membro_principal' => !empty($baixa_farmacia->beneficiario) ? $baixa_farmacia->beneficiario->nome : '',
            'id_processo' => $baixa_farmacia->id,
            'referencia_processo' => !empty($baixa_farmacia->estadoBaixa) ? $baixa_farmacia->estadoBaixa->referencia : '',
            'estado_baixa' => !empty($baixa_farmacia->estadoBaixa) ? $baixa_farmacia->estadoBaixa->nome : '',
            'instituicao_proveniencia' => !empty($baixa_farmacia->farmacia) ? $baixa_farmacia->farmacia->nome : '',
        ];

        $when = now()->addSeconds(10);

        if ($estdo_baixa_codigo == 7) {
            // Pedido de Aprovacao Rejeitado => Beneficiario, Farmacia
            $this->sendToBeneficiario($baixa_farmacia, $data, $when);
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 8) {
            // Pedido de Aprovacao Aguardando Aprovacao => Beneficiario, Empresa
            $this->sendToBeneficiario($baixa_farmacia, $data, $when);
            $this->sendToEmpresa($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 9) {
            // Pedido de Aprovacao Aguardando Inicializacao do Gasto => Beneficiario, Farmacia
            $this->sendToBeneficiario($baixa_farmacia, $data, $when);
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 10) {
            // Aguardando Confirmação => Beneficiario, Empresa
            $this->sendToBeneficiario($baixa_farmacia, $data, $when);
            $this->sendToEmpresa($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 11) {
            // Aguardando Pagamento => Farmacia, Empresa
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
            $this->sendToEmpresa($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 12) {
            // Pagamento Processado => Farmacia
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
        } else if ($estdo_baixa_codigo == 13) {
            // Devolvido => Farmacia
            $this->sendToFarmacia($baixa_farmacia, $data, $when);
        }
    }

    /**
     * Handle the baixa farmacia "deleted" event.
     *
     * @param  \App\BaixaFarmacia  $baixaFarmacia
     * @return void
     */
    public function deleted(BaixaFarmacia $baixaFarmacia)
    {
        //
    }

    /**
     * Handle the baixa farmacia "restored" event.
     *
     * @param  \App\BaixaFarmacia  $baixaFarmacia
     * @return void
     */
    public function restored(BaixaFarmacia $baixaFarmacia)
    {
        //
    }

    /**
     * Handle the baixa farmacia "force deleted" event.
     *
     * @param  \App\BaixaFarmacia  $baixaFarmacia
     * @return void
     */
    public function forceDeleted(BaixaFarmacia $baixaFarmacia)
    {
        //
    }

    protected function sendToBeneficiario($baixa_farmacia, $data, $when)
    {

        if (!empty($beneficiario = $baixa_farmacia->beneficiario)) {
            if (filter_var($beneficiario->email, FILTER_VALIDATE_EMAIL) != false) {
                Mail::to($beneficiario->email)->later($when, new SendBaixaMember($data));
            }
        }
    }

    protected function sendToFarmacia($baixa_farmacia, $data, $when)
    {
        $emails_utilizadores_farmacia = UtilizadorFarmacia::where('farmacia_id', $baixa_farmacia->farmacia_id)
            ->get()
            ->filter(function ($utilizador_farmacia, $key) {
                return filter_var($utilizador_farmacia->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();


        if (filter_var($baixa_farmacia->farmacia->email, FILTER_VALIDATE_EMAIL) != false) {
            Mail::to($baixa_farmacia->farmacia->email)->later($when, new SendBaixaOrganization($data));
        }

        foreach ($emails_utilizadores_farmacia as $key => $email) {
            Mail::to($email)->later($when, new SendBaixaOrganization($data));
        }
    }

    protected function sendToEmpresa($baixa_farmacia, $data, $when)
    {
        $emails_utilizadores_empresa = UtilizadorEmpresa::emails($baixa_farmacia->empresa_id)
            ->get()
            ->filter(function ($utilizador_empresa, $key) {
                return filter_var($utilizador_empresa->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        if (!empty($empresa = $baixa_farmacia->empresa)) {

            if (filter_var($empresa->email, FILTER_VALIDATE_EMAIL)  != false) {
                Mail::to($empresa->email)->later($when, new SendBaixaOrganization($data));
            }
        }

        foreach ($emails_utilizadores_empresa as $key => $email) {
            Mail::to($email)->later($when, new SendBaixaOrganization($data));
        }
    }
}
