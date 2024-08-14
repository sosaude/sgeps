<?php

namespace App\Observers;

use App\Models\User;
use App\Jobs\SendResetPasswordJob;
use App\Mail\SendNewPasswordBeneficiarioMail;
use Illuminate\Support\Facades\Mail;
use App\Models\DependenteBeneficiario;
use App\Models\UtilizadorAdministracao;
use App\Mail\SendNewPasswordUtilizadorMail;
use App\Mail\SendNewPasswordThroughAdminMail;


class DependenteBeneficiarioObserver
{
    /**
     * Handle the dependente beneficiario "created" event.
     *
     * @param  \App\Models\DependenteBeneficiario  $dependenteBeneficiario
     * @return void
     */
    public function created(DependenteBeneficiario $dependente_beneficiario)
    {
        if(app()->runningInConsole()) {
            return;
        }
        
        $codigo_login = 'DEBENE' . sprintf("%04d", $dependente_beneficiario->id);
        $generated_password = "P@ass".uniqid();

        $dependente_beneficiario->load('user');

        $emails_admins = UtilizadorAdministracao::emails()
            ->get()
            ->filter(function ($utilizador_admin, $key) {
                return filter_var($utilizador_admin->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        if($user = $dependente_beneficiario->user) {
            $user->codigo_login = $codigo_login;
            $user->password = bcrypt($generated_password);
            // $user->tenant_id = $dependente_beneficiario->tenant_id;
            $user->save();
            $login_identifier = ['campo' => 'Código de Login', 'valor' => $codigo_login];
            $when = now()->addSeconds(10);


            if (filter_var($dependente_beneficiario->email, FILTER_VALIDATE_EMAIL) != false) {
                Mail::to($dependente_beneficiario->email)->later($when, new SendNewPasswordBeneficiarioMail($user, $login_identifier, $generated_password));
            } else {
                foreach ($emails_admins as $key => $email) {
                    Mail::to($email)->later($when, new SendNewPasswordThroughAdminMail($user, $login_identifier, $generated_password));
                }
            }
        }

        
    }

    /**
     * Handle the dependente beneficiario "updated" event.
     *
     * @param  \App\DependenteBeneficiario  $dependenteBeneficiario
     * @return void
     */
    public function updated(DependenteBeneficiario $dependenteBeneficiario)
    {
        //
    }

    /**
     * Handle the dependente beneficiario "deleted" event.
     *
     * @param  \App\DependenteBeneficiario  $dependenteBeneficiario
     * @return void
     */
    public function deleted(DependenteBeneficiario $dependenteBeneficiario)
    {
        //
        if(app()->runningInConsole()) {
            return;
        }

        $dependenteBeneficiario->user->delete();
    }

    /**
     * Handle the dependente beneficiario "restored" event.
     *
     * @param  \App\DependenteBeneficiario  $dependenteBeneficiario
     * @return void
     */
    public function restored(DependenteBeneficiario $dependenteBeneficiario)
    {
        //
    }

    /**
     * Handle the dependente beneficiario "force deleted" event.
     *
     * @param  \App\DependenteBeneficiario  $dependenteBeneficiario
     * @return void
     */
    public function forceDeleted(DependenteBeneficiario $dependenteBeneficiario)
    {
        //
    }
}
