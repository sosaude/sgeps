<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administracao extends Model
{
    public $fillable = ['nome', 'tenant_id'];

    public function utilizadoresAdministracao()
    {
        return $this->hasMany(UtilizadorAdministracao::class);
    }
}
