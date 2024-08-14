<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Empresa;
use App\Models\UtilizadorEmpresa;
use App\Jobs\SendResetPasswordJob;
use App\Mail\SendNewUtilizadorMail;
use Illuminate\Support\Facades\Mail;
use App\Tenant\Manager\TenantManager;
use App\Mail\SendNewPasswordUtilizadorMail;
use App\Mail\SendNewPasswordThroughAdminMail;
use App\Models\UtilizadorAdministracao;

class UtilizadorEmpresaObserver
{
    /**
     * Handle the utilizador empresa "created" event.
     *
     * @param  \App\Models\UtilizadorEmpresa  $utilizadorEmpresa
     * @return void
     */
    public function creating(UtilizadorEmpresa $utilizador_empresa)
    {
        if (app()->runningInConsole()) {
            return;
        }



        /* $tenant = app(TenantManager::class)->getTenant();

        if ($tenant->id == 1) {

            $new_tenant_id = Empresa::where('id',$utilizador_empresa->empresa_id)->pluck('tenant_id')->first();

            if (!isset($utilizador_empresa->tenant_id)) {
                $utilizador_empresa->setAttribute('tenant_id', $new_tenant_id);
            }

        } */
    }
    /**
     * Handle the utilizador empresa "created" event.
     *
     * @param  \App\Models\UtilizadorEmpresa  $utilizadorEmpresa
     * @return void
     */
    public function created(UtilizadorEmpresa $utilizador_empresa)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /* $codigo_login = 'EMP' . sprintf("%04d", $utilizador_empresa->id);

        $user = new User();
        $user->nome = $utilizador_empresa->nome;
        $user->codigo_login = $codigo_login;
        $user->loged_once = 0;
        $user->login_attempts = 0;
        $user->active = $utilizador_empresa->activo;
        $user->role_id = $utilizador_empresa->role_id;
        $user->tenant_id = $utilizador_empresa->tenant_id;
        $user->utilizador_empresa_id = $utilizador_empresa->id;
        $user->save(); */

        $codigo_login = 'EMP' . sprintf("%04d", $utilizador_empresa->id);
        // $generated_password = "P@ass" . uniqid();
        $generated_password = "P@ass" . uniqid();
        // dd($utilizador_empresa);


        // dd($utilizador_empresa->user);

        /* $user = User::find($utilizador_empresa->user_id);
        $utilizador_empresa->load('empresa');

        if ($user) {
        }

        if ($user->email) {
            SendResetPasswordJob::dispatch($user->email, $user, $generated_password)->delay(now()->addSeconds(10));
        } else {
            // Find the Admin user based on role, to send the new default password, since the user owner is using codigo_login
            $recipient = User::whereHas('role', function ($q) {
                $q->where('codigo', 1);
            })->first();

            if ($recipient->email) {
                SendResetPasswordJob::dispatch($recipient->email, $user, $generated_password)->delay(now()->addSeconds(10));
            }
        } */

        $utilizador_empresa->load('user');
        $check_utilizador_email = filter_var($utilizador_empresa->email, FILTER_VALIDATE_EMAIL);
        $except = [$utilizador_empresa->id];

        $emails_utilizadores_empresa = UtilizadorEmpresa::emails($utilizador_empresa->empresa_id)
            ->whereNotIn('id', $except)
            ->get()
            ->filter(function ($utilizador_empresa, $key) {
                return filter_var($utilizador_empresa->email, FILTER_VALIDATE_EMAIL);
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

        if (!empty($user = $utilizador_empresa->user)) {

            $user = $utilizador_empresa->user;
            $user->codigo_login = $codigo_login;
            $user->password = bcrypt($generated_password);
            // $user->tenant_id = $utilizador_empresa->empresa->tenant_id;
            $user->save();
            $when = now()->addSeconds(10);
            $login_identifier = ['campo' => 'Código de Login', 'valor' => $codigo_login];



            if ($check_utilizador_email != false) {
                Mail::to($utilizador_empresa->email)->later($when, new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password));
            } else {
                foreach ($emails_admins as $key => $email) {
                    Mail::to($email)->later($when, new SendNewPasswordThroughAdminMail($user, $login_identifier, $generated_password));
                }
            }

            foreach ($emails_utilizadores_empresa as $key => $email) {
                Mail::to($email)->later($when, new SendNewUtilizadorMail($utilizador_empresa->nome));
            }
        }
    }

    public function saving(UtilizadorEmpresa $utilizador_empresa)
    {
        
        if ($utilizador_empresa->isDirty('email')) {

            
            $utilizador_empresa->email_verificado = false;
            
            $generated_password = "P@ss" . uniqid();

            $utilizador_empresa->load('user');
            $user = $utilizador_empresa->user;
            $user->password = bcrypt($generated_password);
            $user->save();

            $check_utilizador_email = filter_var($utilizador_empresa->email, FILTER_VALIDATE_EMAIL);
            $login_identifier = ['campo' => 'Código de Login', 'valor' => $user->codigo_login];
            $when = now()->addSeconds(10);

            $emails_admins = UtilizadorAdministracao::emails()
                ->get()
                ->filter(function ($utilizador_admin, $key) {
                    return filter_var($utilizador_admin->email, FILTER_VALIDATE_EMAIL);
                })
                ->pluck('email')
                ->toArray();

            if ($check_utilizador_email != false) {
                Mail::to($utilizador_empresa->email)->later($when, new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password));
            } else {
                foreach ($emails_admins as $key => $email) {
                    Mail::to($email)->later($when, new SendNewPasswordThroughAdminMail($user, $login_identifier, $generated_password));
                }
            }
        }
    }

    /**
     * Handle the utilizador empresa "updated" event.
     *
     * @param  \App\Models\UtilizadorEmpresa  $utilizadorEmpresa
     * @return void
     */
    public function updated(UtilizadorEmpresa $utilizadorEmpresa)
    {
    }

    /**
     * Handle the utilizador empresa "deleted" event.
     *
     * @param  \App\Models\UtilizadorEmpresa  $utilizadorEmpresa
     * @return void
     */
    public function deleted(UtilizadorEmpresa $utilizadorEmpresa)
    {
        //
        $utilizadorEmpresa->user->delete();
    }

    /**
     * Handle the utilizador empresa "restored" event.
     *
     * @param  \App\Models\UtilizadorEmpresa  $utilizadorEmpresa
     * @return void
     */
    public function restored(UtilizadorEmpresa $utilizadorEmpresa)
    {
        //
    }

    /**
     * Handle the utilizador empresa "force deleted" event.
     *
     * @param  \App\Models\UtilizadorEmpresa  $utilizadorEmpresa
     * @return void
     */
    public function forceDeleted(UtilizadorEmpresa $utilizadorEmpresa)
    {
        //
    }
}
