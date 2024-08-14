<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Administracao;
use App\Jobs\SendResetPasswordJob;
use App\Mail\SendNewUtilizadorMail;
use Illuminate\Support\Facades\Mail;
use App\Tenant\Manager\TenantManager;
use App\Models\UtilizadorAdministracao;
use App\Mail\SendNewPasswordUtilizadorMail;

class UtilizadorAdministracaoObserver
{
    /**
     * Handle the utilizador administracao "creating" event.
     *
     * @param  \App\Models\UtilizadorAdministracao  $utilizador_administracao
     * @return void
     */
    public function creating(UtilizadorAdministracao $utilizador_administracao)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /* $tenant = app(TenantManager::class)->getTenant();

        if ($tenant->id == 1) {

            $new_tenant_id = Administracao::find($utilizador_administracao->administracao_id)->pluck('id')->first();

            if (!isset($utilizador_administracao->tenant_id)) {
                $utilizador_administracao->setAttribute('tenant_id', $new_tenant_id);
            }

        } */
    }

    /**
     * Handle the utilizador administracao "created" event.
     *
     * @param  \App\Models\UtilizadorAdministracao  $utilizador_administracao
     * @return void
     */
    public function created(UtilizadorAdministracao $utilizador_administracao)
    {
        if (app()->runningInConsole()) {
            return;
        }

        /* $user = new User();
        $user->nome = $utilizador_administracao->nome;
        $user->email = $utilizador_administracao->email;
        $user->loged_once = 0;
        $user->login_attempts = 0;
        $user->active = $utilizador_administracao->activo;
        $user->role_id = $utilizador_administracao->role_id;
        $user->utilizador_administracao_id = $utilizador_administracao->id;
        $user->save(); */

        $generated_password = "P@ss" . uniqid();

        /* $user = User::find($utilizador_administracao->user_id);
        $utilizador_administracao->load('administracao');

        if($user) {
            $user->email = $utilizador_administracao->email;
            $user->password = bcrypt($generated_password);
            // $user->tenant_id = $utilizador_administracao->administracao->tenant_id;            
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


        $utilizador_administracao->load('user');
        $check_utilizador_email = filter_var($utilizador_administracao->email, FILTER_VALIDATE_EMAIL);
        $except = [$utilizador_administracao->id];

        $emails_utilizadores_administracao = UtilizadorAdministracao::emails()
            ->whereNotIn('id', $except)
            ->get()
            ->filter(function ($utilizador_administracao, $key) {
                return filter_var($utilizador_administracao->email, FILTER_VALIDATE_EMAIL);
            })
            ->pluck('email')
            ->toArray();

        if (!empty($user = $utilizador_administracao->user)) {


            $user->email = $utilizador_administracao->email;
            $user->password = bcrypt($generated_password);
            // $user->tenant_id = $utilizador_empresa->empresa->tenant_id;
            $user->save();
            $when = now()->addSeconds(10);
            $login_identifier = ['campo' => 'Email', 'valor' => $utilizador_administracao->email];

            if ($check_utilizador_email != false) {

                // return new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password);

                Mail::to($utilizador_administracao->email)->later($when, new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password));
            }

            foreach ($emails_utilizadores_administracao as $key => $email) {
                Mail::to($email)->later($when, new SendNewUtilizadorMail($utilizador_administracao->nome));
            }
        }
    }

    public function saving(UtilizadorAdministracao $utilizador_administracao)
    {
        //


        if ($utilizador_administracao->isDirty('email')) {

            $utilizador_administracao->email_verificado = false;

            $generated_password = "P@ss" . uniqid();

            $utilizador_administracao->load('user');
            $utilizador_administracao->load('user');
            $user = $utilizador_administracao->user;
            $user->password = bcrypt($generated_password);
            $user->save();

            $check_utilizador_email = filter_var($utilizador_administracao->email, FILTER_VALIDATE_EMAIL);
            
            $when = now()->addSeconds(10);
            $login_identifier = ['campo' => 'Email', 'valor' => $utilizador_administracao->email];
            

            if ($check_utilizador_email != false) {

                Mail::to($utilizador_administracao->email)->later($when, new SendNewPasswordUtilizadorMail($user, $login_identifier, $generated_password));
            }

        }
    }

    /**
     * Handle the utilizador administracao "updated" event.
     *
     * @param  \App\Models\UtilizadorAdministracao  $utilizadorAdministracao
     * @return void
     */
    public function updated(UtilizadorAdministracao $utilizadorAdministracao)
    {
    }

    /**
     * Handle the utilizador administracao "deleted" event.
     *
     * @param  \App\Models\UtilizadorAdministracao  $utilizadorAdministracao
     * @return void
     */
    public function deleted(UtilizadorAdministracao $utilizadorAdministracao)
    {
        //
    }

    /**
     * Handle the utilizador administracao "restored" event.
     *
     * @param  \App\Models\UtilizadorAdministracao  $utilizadorAdministracao
     * @return void
     */
    public function restored(UtilizadorAdministracao $utilizadorAdministracao)
    {
        //
    }

    /**
     * Handle the utilizador administracao "force deleted" event.
     *
     * @param  \App\Models\UtilizadorAdministracao  $utilizadorAdministracao
     * @return void
     */
    public function forceDeleted(UtilizadorAdministracao $utilizadorAdministracao)
    {
        //
    }
}
