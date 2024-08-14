<?php

namespace App\Observers;

use App\Models\User;
use App\Jobs\SendResetPasswordJob;

class UserObserver
{
    protected static $generated_password;
    /**
     * Handle the user "creating" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function creating(User $user){
        /* self::$generated_password = "P@ss".uniqid();
        $user->password = bcrypt(self::$generated_password); */
    }

    /**
     * Handle the user "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // dd(self::$generated_password);
        /* if($user->email){
            SendResetPasswordJob::dispatch($user->email, $user, self::$generated_password)->delay(now()->addSeconds(10));
        }else{
             // Find the Admin user based on role, to send the new default password, since the user owner is using codigo_login
             $recipient = User::whereHas('role', function ($q) {
                $q->where('codigo', 1);
            })->first();

            if($recipient->email){
                SendResetPasswordJob::dispatch($recipient->email, $user, self::$generated_password)->delay(now()->addSeconds(10));
            }
        } */
        
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    
    }

    /**
     * Handle the user "deleting" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleting(User $user)
    {
        //
        // dd('Apagando');
        $user->update(['active' => false]);
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
