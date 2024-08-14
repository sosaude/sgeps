<?php

namespace App\Observers;

use App\Models\Farmacia;
use App\Models\Medicamento;
use App\Mail\SendMedicamentoMail;
use Illuminate\Support\Facades\Mail;

class MedicamentoObserver
{
    /**
     * Handle the medicamento "created" event.
     *
     * @param  \App\Medicamento  $medicamento
     * @return void
     */
    public function created(Medicamento $medicamento)
    {
        $medicamento->load(['nomeGenerico', 'formaMedicamento', 'grupoMedicamento', 'subGrupoMedicamento', 'subClasseMedicamento']);

        $emails_farmacias = Farmacia::emails()
            ->get()
            ->filter(function ($farmacia) {
                return filter_var($farmacia->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();
        
        $when = now()->addSeconds(10);

        foreach ($emails_farmacias as $key => $email) {
            Mail::to($email)->later($when, new SendMedicamentoMail($medicamento));
        }
    }

    /**
     * Handle the medicamento "updated" event.
     *
     * @param  \App\Medicamento  $medicamento
     * @return void
     */
    public function updated(Medicamento $medicamento)
    {
        //
    }

    /**
     * Handle the medicamento "deleted" event.
     *
     * @param  \App\Medicamento  $medicamento
     * @return void
     */
    public function deleted(Medicamento $medicamento)
    {
        //
    }

    /**
     * Handle the medicamento "restored" event.
     *
     * @param  \App\Medicamento  $medicamento
     * @return void
     */
    public function restored(Medicamento $medicamento)
    {
        //
    }

    /**
     * Handle the medicamento "force deleted" event.
     *
     * @param  \App\Medicamento  $medicamento
     * @return void
     */
    public function forceDeleted(Medicamento $medicamento)
    {
        //
    }
}
