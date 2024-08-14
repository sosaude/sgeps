<?php

namespace App\Observers;

use App\Models\Empresa;
use App\Models\UnidadeSanitaria;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNewOrganizationMail;

class UnidadeSanitariaObserver
{
    /**
     * Handle the unidade sanitaria "created" event.
     *
     * @param  \App\Models\UnidadeSanitaria  $unidadeSanitaria
     * @return void
     */
    public function created(UnidadeSanitaria $unidade_sanitaria)
    {
        $emails_empresas = Empresa::emails()
        ->get()
        ->filter( function ($empresa) {
            return filter_var($empresa->email, FILTER_VALIDATE_EMAIL);
        })
        ->pluck('email')
        ->toArray();
        $when = now()->addSeconds(10);

        foreach ($emails_empresas as $key => $email) {
            Mail::to($email)->later($when, new SendNewOrganizationMail('Unidade Sanitária', $unidade_sanitaria->nome));
        }
    }

    /**
     * Handle the unidade sanitaria "updated" event.
     *
     * @param  \App\Models\UnidadeSanitaria  $unidadeSanitaria
     * @return void
     */
    public function updated(UnidadeSanitaria $unidadeSanitaria)
    {
        //
    }

    /**
     * Handle the unidade sanitaria "deleted" event.
     *
     * @param  \App\Models\UnidadeSanitaria  $unidadeSanitaria
     * @return void
     */
    public function deleted(UnidadeSanitaria $unidadeSanitaria)
    {
        //
        $unidadeSanitaria->tenant->delete();
    }

    /**
     * Handle the unidade sanitaria "restored" event.
     *
     * @param  \App\Models\UnidadeSanitaria  $unidadeSanitaria
     * @return void
     */
    public function restored(UnidadeSanitaria $unidadeSanitaria)
    {
        //
    }

    /**
     * Handle the unidade sanitaria "force deleted" event.
     *
     * @param  \App\Models\UnidadeSanitaria  $unidadeSanitaria
     * @return void
     */
    public function forceDeleted(UnidadeSanitaria $unidadeSanitaria)
    {
        //
    }
}
