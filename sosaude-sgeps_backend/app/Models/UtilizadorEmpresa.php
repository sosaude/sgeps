<?php

namespace App\Models;

use App\Tenant\Traits\ForTenants;
use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UtilizadorEmpresa
 * @package App\Models
 * @version March 24, 2020, 11:24 am UTC
 *
 * @property \App\Models\Role role
 * @property \App\Models\Empresa empresa
 * @property \Illuminate\Database\Eloquent\Collection users
 * @property string nome
 * @property integer empresa_id
 * @property string contacto
 * @property boolean activo
 * @property integer role_id
 * @property string nacionalidade
 * @property string observacoes
 */
class UtilizadorEmpresa extends Model
{
    use SoftDeletes;
    // use ForTenants;

    public $table = 'utilizador_empresas';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];



    public $fillable = [
        'nome',
        'email',
        'email_verificado',
        'empresa_id',
        'contacto',
        'nacionalidade',
        'observacoes',
        'activo',
        'role_id',
        'user_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
        'email' => 'string',
        'empresa_id' => 'integer',
        'contacto' => 'string',
        'activo' => 'boolean',
        'role_id' => 'integer',
        'nacionalidade' => 'string',
        'observacoes' => 'string',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class, 'empresa_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function getActivoAttribute()
    {
        if ($this->attributes['activo'] == 1) {
            return 1;
        }

        return 0;
    }

    /**
     * Scope a query to only include utilizador_empresa with Gestor Empresa role
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGestorEmpresa($query)
    {
        return $query->whereHas('role', function ($q_role) {
            return $q_role->where('codigo', 4);
        });
    }

    public function scopeByEmpresa($query, $empresa_id)
    {
        return $query->where('empresa_id', $empresa_id);
    }

    public function scopeEmails($query, $empresa_id)
    {
        return $query
            ->where('empresa_id', $empresa_id)
            ->where('email', '!=', null)
            ->where('email', '!=', '')
            ;
    }
}
