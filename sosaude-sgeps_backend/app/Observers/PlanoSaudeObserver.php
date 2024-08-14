<?php

namespace App\Observers;

use App\Models\PlanoSaude;
use App\Models\Beneficiario;
use App\Mail\SendPlanoSaudeMail;
use Illuminate\Support\Facades\Mail;

class PlanoSaudeObserver
{
    /**
     * Handle the plano saude "created" event.
     *
     * @param  \App\PlanoSaude  $planoSaude
     * @return void
     */
    public function created(PlanoSaude $plano_saude)
    {
        if (!empty($plano_saude->grupo_beneficiario_id)) {
            $emails_beneficiarios = Beneficiario::where('email', '!=', null)
            ->where('email', '!=', '')
            ->where('grupo_beneficiario_id', $plano_saude->grupo_beneficiario_id)
            ->get()
            ->filter( function ($beneficiario) {
                return filter_var($beneficiario->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();
            $when = now()->addSeconds(10);

            foreach ($emails_beneficiarios as $key => $email) {
                Mail::to($email)->later($when, new SendPlanoSaudeMail());
            }
        }
    }

    /**
     * Handle the plano saude "updated" event.
     *
     * @param  \App\PlanoSaude  $planoSaude
     * @return void
     */
    public function updated(PlanoSaude $plano_saude)
    {
        
    }

    /**
     * Handle the plano saude "deleted" event.
     *
     * @param  \App\PlanoSaude  $planoSaude
     * @return void
     */
    public function deleted(PlanoSaude $planoSaude)
    {
        //
    }

    /**
     * Handle the plano saude "restored" event.
     *
     * @param  \App\PlanoSaude  $planoSaude
     * @return void
     */
    public function restored(PlanoSaude $planoSaude)
    {
        //
    }

    /**
     * Handle the plano saude "force deleted" event.
     *
     * @param  \App\PlanoSaude  $planoSaude
     * @return void
     */
    public function forceDeleted(PlanoSaude $planoSaude)
    {
        //
    }
}
