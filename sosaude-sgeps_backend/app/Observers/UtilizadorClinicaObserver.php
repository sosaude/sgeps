<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Clinica;
use App\Models\UtilizadorClinica;
use App\Jobs\SendResetPasswordJob;
use App\Tenant\Manager\TenantManager;

class UtilizadorClinicaObserver
{
    /**
     * Handle the utilizador clinica "creating" event.
     *
     * @param  \App\Models\UtilizadorFarmacia  $utilizador_clinica
     * @return void
     */
    public function creating(UtilizadorClinica $utilizador_clinica)
    {
        if(app()->runningInConsole()) {
            return;
        }
        
        /* $tenant = app(TenantManager::class)->getTenant();

        if ($tenant->id == 1) {

            $new_tenant_id = Clinica::where('id',$utilizador_clinica->clinica_id)->pluck('tenant_id')->first();

            if (!isset($utilizador_clinica->tenant_id)) {
                $utilizador_clinica->setAttribute('tenant_id', $new_tenant_id);
            }

        } */

    }

    /**
     * Handle the utilizador clinica "created" event.
     *
     * @param  \App\Models\UtilizadorClinica  $utilizadorClinica
     * @return void
     */
    public function created(UtilizadorClinica $utilizador_clinica)
    {
        if(app()->runningInConsole()) {
            return;
        }
        
        /* $codigo_login = 'CLI' . sprintf("%04d", $utilizador_clinica->id);

        $user = new User();
        $user->nome = $utilizador_clinica->nome;
        $user->codigo_login = $codigo_login;
        $user->loged_once = 0;
        $user->login_attempts = 0;
        $user->active = $utilizador_clinica->activo;
        $user->role_id = $utilizador_clinica->role_id;
        $user->utilizador_clinica_id = $utilizador_clinica->id;
        $user->save(); */

        $codigo_login = 'CLI' . sprintf("%04d", $utilizador_clinica->id);
        $generated_password = "1234567";

        $user = User::find($utilizador_clinica->user_id);

        if($user) {
            $user->codigo_login = $codigo_login;
            $user->password = bcrypt($generated_password);
            // $user->tenant_id = $utilizador_clinica->tenant_id;
            $user->save();
        }

        if($user->email){
            SendResetPasswordJob::dispatch($user->email, $user, $generated_password)->delay(now()->addSeconds(10));
        }else{
             // Find the Admin user based on role, to send the new default password, since the user owner is using codigo_login
             $recipient = User::whereHas('role', function ($q) {
                $q->where('codigo', 1);
            })->first();

            if($recipient->email){
                SendResetPasswordJob::dispatch($recipient->email, $user, $generated_password)->delay(now()->addSeconds(10));
            }
        }
    }

    /**
     * Handle the utilizador clinica "updated" event.
     *
     * @param  \App\Models\UtilizadorClinica  $utilizadorClinica
     * @return void
     */
    public function updated(UtilizadorClinica $utilizadorClinica)
    {
        //
    }

    /**
     * Handle the utilizador clinica "deleted" event.
     *
     * @param  \App\Models\UtilizadorClinica  $utilizadorClinica
     * @return void
     */
    public function deleted(UtilizadorClinica $utilizadorClinica)
    {
        //
        if(app()->runningInConsole()) {
            return;
        }
        $utilizadorClinica->user->delete();
    }

    /**
     * Handle the utilizador clinica "restored" event.
     *
     * @param  \App\Models\UtilizadorClinica  $utilizadorClinica
     * @return void
     */
    public function restored(UtilizadorClinica $utilizadorClinica)
    {
        //
    }

    /**
     * Handle the utilizador clinica "force deleted" event.
     *
     * @param  \App\Models\UtilizadorClinica  $utilizadorClinica
     * @return void
     */
    public function forceDeleted(UtilizadorClinica $utilizadorClinica)
    {
        //
    }
}
