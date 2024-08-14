<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Role
 * @package App\Models
 * @version March 16, 2020, 8:46 am UTC
 *
 * @property \App\Models\Seccao seccao
 * @property \Illuminate\Database\Eloquent\Collection users
 * @property integer codigo
 * @property string role
 * @property integer seccao_id
 */
class Role extends Model
{

    public $table = 'roles';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // protected $hidden = ['id', 'role'];



    public $fillable = [
        'codigo',
        'role',
        'seccao_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'codigo' => 'integer',
        'role' => 'string',
        'seccao_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'codigo' => 'required',
        'role' => 'required',
        'seccao_id' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function seccao()
    {
        return $this->belongsTo(\App\Models\Seccao::class, 'seccao_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function users()
    {
        return $this->hasMany(\App\Models\User::class, 'role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function utilizadoresFarmacia()
    {
        return $this->hasMany(\App\Models\UtilizadorFarmacia::class, 'role_id');
    }

    /**
     * Scope a query to only include "Gestor Empresa" role(codigo=4): Gestor Empresa
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGestorEmpresa($query)
    {
        return $query->where('codigo', 4);
    }

    /**
     * Scope a query to only include roles from Seccao Farmacia, but not only the managers
     */
    public function scopeByUtilizadoresEmpresa($query)
    {
        return $query->whereNotIn('codigo', [8,9])->whereHas('seccao', function ($q_seccao) {
            return $q_seccao->where('seccao_id', 2);
        });
    }

    /**
     * Scope a query to only include roles from Seccao Empresa
     */
    public function scopeBySeccaoEmpresa($query)
    {
        return $query->whereHas('seccao', function ($q_seccao) {
            return $q_seccao->where('seccao_id', 2);
        });
    }

    /**
     * Scope a query to only include roles from Seccao Empresa, but not only the managers
     */
    public function scopeBySeccaoFarmacia($query)
    {
        return $query->whereHas('seccao', function ($q_seccao) {
            return $q_seccao->where('seccao_id', 3);
        });
    }
    
    /**
     * Scope a query to only include "Gestor Clinica" role(codigo=6): Gestor Clinica
     */
    public function scopeGestorClinica($query)
    {
        return $query->where('codigo', 6);
    }
    public function scopeGestorUnidadeSanitaria($query)
    {
        return $query->where('codigo', 6);
    }

    /**
     * Scope a query to only include roles from Seccao Clinica
     */
    public function scopeBySeccaoClinica($query)
    {
        return $query->whereHas('seccao', function ($q_seccao) {
            return $q_seccao->where('seccao_id', 4);
        });
    }
    
    public function scopeBySeccaoUnidadeSanitaria($query)
    {
        return $query->whereHas('seccao', function ($q_seccao) {
            return $q_seccao->where('seccao_id', 4);
        });
    }
}
