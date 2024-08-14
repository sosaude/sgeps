<?php

namespace App\Models;

use App\Tenant\Traits\ForTenants;
use Eloquent as Model;

/**
 * Class GrupoBeneficiario
 * @package App\Models
 * @version May 20, 2020, 5:33 pm UTC
 *
 */
class GrupoBeneficiario extends Model
{
    // use ForTenants;

    public $fillable = [
        'nome',
        'empresa_id'
    ];

    public $hidden = ['tenant_id'];

    public $appends = [/* 'numero_beneficiarios' */];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
    ];

    public function scopeByEmpresa($query, $empresa_id)
    {
        return $query->where('empresa_id', $empresa_id);
    }

    public function beneficiarios()
    {
        return $this->hasMany(Beneficiario::class);
    }

    public function planoSaude()
    {
        return $this->hasOne(PlanoSaude::class);
    }

    /* public function getNumeroBeneficiariosAttribute()
    {
        if ($this->beneficiarios->count()) {
            return $this->beneficiarios->count();
        }

        return 0;
    } */

}
