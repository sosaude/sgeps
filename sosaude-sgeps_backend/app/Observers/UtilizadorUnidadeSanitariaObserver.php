<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UnidadeSanitaria;
use App\Jobs\SendResetPasswordJob;
use App\Mail\SendNewUtilizadorMail;
use Illuminate\Support\Facades\Mail;
use App\Tenant\Manager\TenantManager;
use App\Models\UtilizadorAdministracao;
use App\Models\UtilizadorUnidadeSanitaria;
use App\Mail\SendNewPasswordUtilizadorMail;
use App\Mail\SendNewPasswordThroughAdminMail;

class UtilizadorUnidadeSanitariaObserver
{
    /**
     * Handle the utilizador clinica "creating" event.
     *
     * @param  \App\Models\UtilizadorUnidadeSanitaria  $utilizador_unidade_santaria
     * @return void
     */
    public function creating(UtilizadorUnidadeSanitaria $utilizador_unidade_santaria)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /* $tenant = app(TenantManager::class)->getTenant();

        if ($tenant->id == 1) {

            $new_tenant_id = UnidadeSanitaria::where('id',$utilizador_unidade_santaria->unidade_sanitaria_id)->pluck('tenant_id')->first();

            if (!isset($utilizador_unidade_santaria->tenant_id)) {
                $utilizador_unidade_santaria->setAttribute('tenant_id', $new_tenant_id);
            }

        } */
    }

    /**
     * Handle the UtilizadorUnidadeSanitaria "created" event.
     *
     * @param  \App\Models\UtilizadorUnidadeSanitaria  $utilizador_unidade_santaria
     * @return void
     */
    public function created(UtilizadorUnidadeSanitaria $utilizador_unidade_santaria)
    {
        if (app()->runningInConsole()) {
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

        $codigo_login = 'UNIS' . sprintf("%04d", $utilizador_unidade_santaria->id);
        $check_utilizador_email = filter_var($utilizador_unidade_santaria->email, FILTER_VALIDATE_EMAIL);
        $generated_password = "P@ss" . uniqid();

        $utilizador_unidade_santaria->load('user');
        $except = [$utilizador_unidade_santaria->id];

        $emails_utilizadores_unidades_sanitarias = UtilizadorUnidadeSanitaria::where('unidade_sanitaria_id', $utilizador_unidade_santaria->unidade_sanitaria_id)
            ->whereNotIn('id', $except)
            ->get()
            ->filter(function ($utilizador_unidade_santaria, $key) {
                return filter_var($utilizador_unidade_santaria->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')->toArray();

        $emails_admins = UtilizadorAdministracao::emails()
            ->get()
            ->filter(function ($utilizador_admin, $key) {
                return filter_var($utilizador_admin->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        if (!empty($user = $utilizador_unidade_santaria->user)) {

            $user->codigo_login = $codigo_login;
            $user->password = bcrypt($generated_password);
            // $user->tenant_id = $utilizador_empresa->empresa->tenant_id;
            $user->save();
            $when = now()->addSeconds(10);
            $login_identifier = ['campo' => 'Código de Login', 'valor' => $codigo_login];

            if ($check_utilizador_email != false) {
                Mail::to($utilizador_unidade_santaria->email)->later($when, new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password));
            } else {
                foreach ($emails_admins as $key => $email) {
                    Mail::to($email)->later($when, new SendNewPasswordThroughAdminMail($user, $login_identifier, $generated_password));
                }
            }

            foreach ($emails_utilizadores_unidades_sanitarias as $key => $email) {
                Mail::to($email)->later($when, new SendNewUtilizadorMail($utilizador_unidade_santaria->nome));
            }
        }
    }

    public function saving(UtilizadorUnidadeSanitaria $utilizador_unidade_santaria)
    {
        if ($utilizador_unidade_santaria->isDirty('email')) {

            $utilizador_unidade_santaria->email_verificado = false;

            $generated_password = "P@ss" . uniqid();

            $utilizador_unidade_santaria->load('user');
            $user = $utilizador_unidade_santaria->user;
            $user->password = bcrypt($generated_password);
            $user->save();

            $check_utilizador_email = filter_var($utilizador_unidade_santaria->email, FILTER_VALIDATE_EMAIL);
            $login_identifier = ['campo' => 'Código de Login', 'valor' => $user->codigo_login];
            
            

            $emails_admins = UtilizadorAdministracao::emails()
            ->get()
            ->filter(function ($utilizador_admin, $key) {
                return filter_var($utilizador_admin->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

            $when = now()->addSeconds(10);
            if ($check_utilizador_email != false) {
                Mail::to($utilizador_unidade_santaria->email)->later($when, new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password));
            } else {
                foreach ($emails_admins as $key => $email) {
                    Mail::to($email)->later($when, new SendNewPasswordThroughAdminMail($user, $login_identifier, $generated_password));
                }
            }
        }
    }


    public function updated(UtilizadorUnidadeSanitaria $utilizador_unidade_santaria)
    {
    }

    /**
     * Handle the UtilizadorUnidadeSanitaria "deleted" event.
     *
     * @param  \App\Models\UtilizadorUnidadeSanitaria  $utilizador_unidade_santaria
     * @return void
     */
    public function deleted(UtilizadorUnidadeSanitaria $utilizador_unidade_santaria)
    {
        //
        if (app()->runningInConsole()) {
            return;
        }

        $utilizador_unidade_santaria->user->delete();
    }
}
