<?php

namespace App\Observers;

use App\Models\Servico;
use App\Models\UnidadeSanitaria;
use App\Mail\SendMedicamentoMail;
use App\Mail\SendServicoMail;
use Illuminate\Support\Facades\Mail;

class ServicoObserver
{
    /**
     * Handle the servico "created" event.
     *
     * @param  \App\Servico  $servico
     * @return void
     */
    public function created(Servico $servico)
    {
        $servico->load(['categoriaServico']);
        
        $emails_unidades_sanitarias = UnidadeSanitaria::emails()
        ->get()
        ->filter( function ($unidade_sanitaria) {
            return filter_var($unidade_sanitaria->email, FILTER_VALIDATE_EMAIL);
        })
        ->pluck('email')
        ->toArray();
        $when = now()->addSeconds(10);

        foreach ($emails_unidades_sanitarias as $key => $email) {
            Mail::to($email)->later($when, new SendServicoMail($servico));
        }
    }

    /**
     * Handle the servico "updated" event.
     *
     * @param  \App\Servico  $servico
     * @return void
     */
    public function updated(Servico $servico)
    {
        //
    }

    /**
     * Handle the servico "deleted" event.
     *
     * @param  \App\Servico  $servico
     * @return void
     */
    public function deleted(Servico $servico)
    {
        //
    }

    /**
     * Handle the servico "restored" event.
     *
     * @param  \App\Servico  $servico
     * @return void
     */
    public function restored(Servico $servico)
    {
        //
    }

    /**
     * Handle the servico "force deleted" event.
     *
     * @param  \App\Servico  $servico
     * @return void
     */
    public function forceDeleted(Servico $servico)
    {
        //
    }
}
