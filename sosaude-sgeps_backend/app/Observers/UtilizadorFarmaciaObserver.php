<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Farmacia;
use App\Jobs\SendResetPasswordJob;
use App\Models\UtilizadorFarmacia;
use App\Mail\SendNewUtilizadorMail;
use Illuminate\Support\Facades\Mail;
use App\Tenant\Manager\TenantManager;
use App\Models\UtilizadorAdministracao;
use App\Mail\SendNewPasswordUtilizadorMail;
use App\Mail\SendNewPasswordThroughAdminMail;

class UtilizadorFarmaciaObserver
{
    /**
     * Handle the utilizador farmacia "creating" event.
     *
     * @param  \App\Models\UtilizadorFarmacia  $utilizador_farmacia
     * @return void
     */
    public function creating(UtilizadorFarmacia $utilizador_farmacia)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /* $tenant = app(TenantManager::class)->getTenant();

        if ($tenant->id == 1) {

            $new_tenant_id = Farmacia::where('id',$utilizador_farmacia->farmacia_id)->pluck('tenant_id')->first();

            if (!isset($utilizador_farmacia->tenant_id)) {
                $utilizador_farmacia->setAttribute('tenant_id', $new_tenant_id);
            }

        } */
    }

    /**
     * Handle the utilizador farmacia "created" event.
     *
     * @param  \App\Models\UtilizadorFarmacia  $utilizador_farmacia
     * @return void
     */
    public function created(UtilizadorFarmacia $utilizador_farmacia)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /* $codigo_login = 'FAR' . sprintf("%04d", $utilizador_farmacia->id);

        $user = new User();
        $user->nome = $utilizador_farmacia->nome;
        $user->codigo_login = $codigo_login;
        $user->loged_once = 0;
        $user->login_attempts = 0;
        $user->active = $utilizador_farmacia->activo;
        $user->role_id = $utilizador_farmacia->role_id;
        $user->utilizador_farmacia_id = $utilizador_farmacia->id;
        $user->save(); */


        $codigo_login = 'FAR' . sprintf("%04d", $utilizador_farmacia->id);
        $generated_password = "P@ss" . uniqid();

        $utilizador_farmacia->load('user');
        $check_utilizador_email = filter_var($utilizador_farmacia->email, FILTER_VALIDATE_EMAIL);
        $except = [$utilizador_farmacia->id];

        $emails_utilizadores_farmacias = UtilizadorFarmacia::where('farmacia_id', $utilizador_farmacia->farmacia_id)
            ->whereNotIn('id', $except)
            ->get()
            ->filter(function ($utilizador_farmacia, $key) {
                return filter_var($utilizador_farmacia->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        $emails_admins = UtilizadorAdministracao::emails()
            ->get()
            ->filter(function ($utilizador_admin, $key) {
                return filter_var($utilizador_admin->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        if (!empty($user = $utilizador_farmacia->user)) {

            $user->codigo_login = $codigo_login;
            $user->password = bcrypt($generated_password);
            $user->save();
            $when = now()->addSeconds(10);
            $login_identifier = ['campo' => 'Código de Login', 'valor' => $codigo_login];

            if ($check_utilizador_email != false) {
                Mail::to($utilizador_farmacia->email)->later($when, new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password));
            } else {
                foreach ($emails_admins as $key => $email) {
                    Mail::to($email)->later($when, new SendNewPasswordThroughAdminMail($user, $login_identifier, $generated_password));
                }
            }

            foreach ($emails_utilizadores_farmacias as $key => $email) {
                Mail::to($email)->later($when, new SendNewUtilizadorMail($utilizador_farmacia->nome));
            }
        }
    }

    public function saving(UtilizadorFarmacia $utilizador_farmacia)
    {
        if ($utilizador_farmacia->isDirty('email')) {

            $utilizador_farmacia->email_verificado = false;

            $generated_password = "P@ss" . uniqid();            

            $utilizador_farmacia->load('user');
            $user = $utilizador_farmacia->user;
            $user->password = bcrypt($generated_password);
            $user->save();

            $check_utilizador_email = filter_var($utilizador_farmacia->email, FILTER_VALIDATE_EMAIL);
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
                Mail::to($utilizador_farmacia->email)->later($when, new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password));
            } else {
                foreach ($emails_admins as $key => $email) {
                    Mail::to($email)->later($when, new SendNewPasswordThroughAdminMail($user, $login_identifier, $generated_password));
                }
            }
        }
    }

    /**
     * Handle the utilizador farmacia "updated" event.
     *
     * @param  \App\Models\UtilizadorFarmacia  $utilizadorFarmacia
     * @return void
     */
    public function updated(UtilizadorFarmacia $utilizadorFarmacia)
    {
    }

    /**
     * Handle the utilizador farmacia "deleted" event.
     *
     * @param  \App\Models\UtilizadorFarmacia  $utilizadorFarmacia
     * @return void
     */
    public function deleted(UtilizadorFarmacia $utilizadorFarmacia)
    {
        //
        /* if(app()->runningInConsole()) {
            return;
        }

        $utilizadorFarmacia->user->delete(); */
    }

    /**
     * Handle the utilizador farmacia "restored" event.
     *
     * @param  \App\Models\UtilizadorFarmacia  $utilizadorFarmacia
     * @return void
     */
    public function restored(UtilizadorFarmacia $utilizadorFarmacia)
    {
        //
    }

    /**
     * Handle the utilizador farmacia "force deleted" event.
     *
     * @param  \App\Models\UtilizadorFarmacia  $utilizadorFarmacia
     * @return void
     */
    public function forceDeleted(UtilizadorFarmacia $utilizadorFarmacia)
    {
        //
    }
}
