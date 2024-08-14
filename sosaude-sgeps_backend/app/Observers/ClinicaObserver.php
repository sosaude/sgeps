<?php

namespace App\Observers;

use App\Models\Clinica;

class ClinicaObserver
{
    /**
     * Handle the clinica "created" event.
     *
     * @param  \App\Models\Clinica  $clinica
     * @return void
     */
    public function created(Clinica $clinica)
    {
        //
    }

    /**
     * Handle the clinica "updated" event.
     *
     * @param  \App\Models\Clinica  $clinica
     * @return void
     */
    public function updated(Clinica $clinica)
    {
        //
    }

    /**
     * Handle the clinica "deleted" event.
     *
     * @param  \App\Models\Clinica  $clinica
     * @return void
     */
    public function deleted(Clinica $clinica)
    {
        //
        if(app()->runningInConsole()) {
            return;
        }

        $clinica->tenant()->delete();
    }

    /**
     * Handle the clinica "restored" event.
     *
     * @param  \App\Models\Clinica  $clinica
     * @return void
     */
    public function restored(Clinica $clinica)
    {
        //
    }

    /**
     * Handle the clinica "force deleted" event.
     *
     * @param  \App\Models\Clinica  $clinica
     * @return void
     */
    public function forceDeleted(Clinica $clinica)
    {
        //
    }
}
