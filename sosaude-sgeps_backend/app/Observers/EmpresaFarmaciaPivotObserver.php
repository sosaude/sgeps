<?php

namespace App\Observers;

use App\Models\Empresa;
use App\Models\EmpresaFarmaciaPivot;


class EmpresaFarmaciaPivotObserver
{
    /**
     * Handle the empresa farmacia pivot "created" event.
     *
     * @param  \App\EmpresaFarmaciaPivot  $empresaFarmaciaPivot
     * @return void
     */
    public function created(EmpresaFarmaciaPivot $empresa_farmacia_pivot)
    {
        //
        /* $empresa = Empresa::where('id', $empresa_farmacia_pivot->empresa_id)
            ->first();

        $farmacias = $empresa->farmacias;

        dd($empresa_farmacia_pivot); */
    }

    /**
     * Handle the empresa farmacia pivot "updated" event.
     *
     * @param  \App\EmpresaFarmaciaPivot  $empresaFarmaciaPivot
     * @return void
     */
    public function updated(EmpresaFarmaciaPivot $empresa_farmaciaPivot)
    {
        //
    }

    /**
     * Handle the empresa farmacia pivot "deleted" event.
     *
     * @param  \App\EmpresaFarmaciaPivot  $empresaFarmaciaPivot
     * @return void
     */
    public function deleted(EmpresaFarmaciaPivot $empresaFarmaciaPivot)
    {
        //
    }

    /**
     * Handle the empresa farmacia pivot "restored" event.
     *
     * @param  \App\EmpresaFarmaciaPivot  $empresaFarmaciaPivot
     * @return void
     */
    public function restored(EmpresaFarmaciaPivot $empresaFarmaciaPivot)
    {
        //
    }

    /**
     * Handle the empresa farmacia pivot "force deleted" event.
     *
     * @param  \App\EmpresaFarmaciaPivot  $empresaFarmaciaPivot
     * @return void
     */
    public function forceDeleted(EmpresaFarmaciaPivot $empresaFarmaciaPivot)
    {
        //
    }
}
