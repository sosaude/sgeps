<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Beneficiario;
use App\Jobs\SendResetPasswordJob;
use App\Mail\SendNewPasswordBeneficiarioMail;
use Illuminate\Support\Facades\Mail;
use App\Models\UtilizadorAdministracao;
use App\Mail\SendNewPasswordUtilizadorMail;
use App\Mail\SendNewPasswordThroughAdminMail;

class BeneficiarioObserver
{
    /**
     * Handle the beneficiario "created" event.
     *
     * @param  \App\Beneficiario  $beneficiario
     * @return void
     */
    public function created(Beneficiario $beneficiario)
    {
        if (app()->runningInConsole()) {
            return;
        }

        $codigo_login = 'BENE' . sprintf("%04d", $beneficiario->id);
        $generated_password = "P@ss" . uniqid();

        /* $user = User::find($beneficiario->user_id);

        if($user) {
            $user->codigo_login = $codigo_login;
            $user->password = bcrypt($generated_password);
            // $user->tenant_id = $beneficiario->tenant_id;
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
        } */

        $beneficiario->load('user');

        $emails_admins = UtilizadorAdministracao::emails()
            ->get()
            ->filter(function ($utilizador_admin, $key) {
                return filter_var($utilizador_admin->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        if (!empty($user = $beneficiario->user)) {

            $user->codigo_login = $codigo_login;
            $user->password = bcrypt($generated_password);
            $user->save();
            $when = now()->addSeconds(10);
            $login_identifier = ['campo' => 'Código de Login', 'valor' => $codigo_login];

            if (filter_var($beneficiario->email, FILTER_VALIDATE_EMAIL) != false) {
                Mail::to($beneficiario->email)->later($when, new SendNewPasswordBeneficiarioMail($user, $login_identifier, $generated_password));
            } else {
                foreach ($emails_admins as $key => $email) {
                    Mail::to($email)->later($when, new SendNewPasswordThroughAdminMail($user, $login_identifier, $generated_password));
                }
            }
        }
    }

    /**
     * Handle the beneficiario "updated" event.
     *
     * @param  \App\Beneficiario  $beneficiario
     * @return void
     */
    public function updated(Beneficiario $beneficiario)
    {
        if (app()->runningInConsole()) {
            return;
        }

        $user = User::find($beneficiario->user_id);

        if ($user) {
            $user->nome = $beneficiario->nome;
            $user->save();
        }
    }

    /**
     * Handle the beneficiario "deleted" event.
     *
     * @param  \App\Beneficiario  $beneficiario
     * @return void
     */
    public function deleted(Beneficiario $beneficiario)
    {
        //
        if (app()->runningInConsole()) {
            return;
        }

        $beneficiario->user->delete();
    }

    /**
     * Handle the beneficiario "restored" event.
     *
     * @param  \App\Beneficiario  $beneficiario
     * @return void
     */
    public function restored(Beneficiario $beneficiario)
    {
        //
    }

    /**
     * Handle the beneficiario "force deleted" event.
     *
     * @param  \App\Beneficiario  $beneficiario
     * @return void
     */
    public function forceDeleted(Beneficiario $beneficiario)
    {
        //
    }
}
