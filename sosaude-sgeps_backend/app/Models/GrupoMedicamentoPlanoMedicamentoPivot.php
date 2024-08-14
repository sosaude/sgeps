<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GrupoMedicamentoPlanoMedicamentoPivot extends Pivot
{
    protected $casts = [
        'coberto' => 'boolean',
        'pre_autorizacao' => 'boolean'
    ];
}
