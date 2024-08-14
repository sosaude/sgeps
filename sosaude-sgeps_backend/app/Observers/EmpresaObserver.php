<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Models\Empresa;
use App\Models\Farmacia;
use App\Models\UnidadeSanitaria;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNewOrganizationMail;

class EmpresaObserver
{
    /**
     * Handle the empresa "created" event.
     *
     * @param  \App\odel=Models\Empresa  $empresa
     * @return void
     */
    public function created(Empresa $empresa)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /* $tenant = new Tenant();
        $tenant->nome = $empresa->nome;
        $tenant->empresa_id = $empresa->id;
        $tenant->save(); */
        $farmacias_emails = Farmacia::emails()
        ->get()
        ->filter( function ($farmacia) {
            return filter_var($farmacia->email, FILTER_VALIDATE_EMAIL);
        })
        ->pluck('email')
        ->toArray();

        $unidades_sanitarias_emails = UnidadeSanitaria::emails()
        ->get()
        ->filter( function ($unidade_sanitaria) {
            return filter_var($unidade_sanitaria->email, FILTER_VALIDATE_EMAIL);
        })
        ->pluck('email')->toArray();

        $emails = array_merge($farmacias_emails, $unidades_sanitarias_emails);
        $when = now()->addSeconds(10);

        foreach ($emails as $email) {
            Mail::to($email)->later($when, new SendNewOrganizationMail('Empresa', $empresa->nome));
        }
    }

    /**
     * Handle the empresa "updated" event.
     *
     * @param  \App\odel=Models\Empresa  $empresa
     * @return void
     */
    public function updated(Empresa $empresa)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /* $tenant = Tenant::where('empresa_id', $empresa->id)->first();
        $tenant->nome = $empresa->nome;
        $tenant->save(); */
    }

    /**
     * Handle the empresa "deleted" event.
     *
     * @param  \App\odel=Models\Empresa  $empresa
     * @return void
     */
    public function deleted(Empresa $empresa)
    {
        //
        if (app()->runningInConsole()) {
            return;
        }

        // $empresa->tenant->delete();
    }

    /**
     * Handle the empresa "restored" event.
     *
     * @param  \App\odel=Models\Empresa  $empresa
     * @return void
     */
    public function restored(Empresa $empresa)
    {
        //
    }

    /**
     * Handle the empresa "force deleted" event.
     *
     * @param  \App\odel=Models\Empresa  $empresa
     * @return void
     */
    public function forceDeleted(Empresa $empresa)
    {
        //
    }
}
