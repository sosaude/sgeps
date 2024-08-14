<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Models\Empresa;
use App\Models\Farmacia;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNewOrganizationMail;

class FarmaciaObserver
{
    /**
     * Handle the farmacia "created" event.
     *
     * @param  \App\Farmacia  $farmacia
     * @return void
     */
    public function created(Farmacia $farmacia)
    {
        if(app()->runningInConsole()) {
            return;
        }

        //
        /* $tenant = new Tenant();
        $tenant->nome = $farmacia->nome;
        $tenant->farmacia_id = $farmacia->id;
        $tenant->save(); */
        $emails_empresas = Empresa::emails()
        ->get()
        ->filter( function ($empresa) {
            return filter_var($empresa->email, FILTER_VALIDATE_EMAIL);
        })
        ->pluck('email')
        ->toArray();
        
        $when = now()->addSeconds(10);

        foreach ($emails_empresas as $key => $email) {
            Mail::to($email)->later($when, new SendNewOrganizationMail('Farmácia', $farmacia->nome));
        }
    }

    /**
     * Handle the farmacia "updated" event.
     *
     * @param  \App\Farmacia  $farmacia
     * @return void
     */
    public function updated(Farmacia $farmacia)
    {
        if(app()->runningInConsole()) {
            return;
        }
        
        //
        /* $tenant = Tenant::where('farmacia_id', $farmacia->id)->first();
        $tenant->nome = $farmacia->nome;
        $tenant->save(); */
    }

    /**
     * Handle the farmacia "deleted" event.
     *
     * @param  \App\Farmacia  $farmacia
     * @return void
     */
    public function deleted(Farmacia $farmacia)
    {
        //
        if(app()->runningInConsole()) {
            return;
        }
        
        $farmacia->tenant->delete();
    }

    /**
     * Handle the farmacia "restored" event.
     *
     * @param  \App\Farmacia  $farmacia
     * @return void
     */
    public function restored(Farmacia $farmacia)
    {
        //
    }

    /**
     * Handle the farmacia "force deleted" event.
     *
     * @param  \App\Farmacia  $farmacia
     * @return void
     */
    public function forceDeleted(Farmacia $farmacia)
    {
        //
    }
}
