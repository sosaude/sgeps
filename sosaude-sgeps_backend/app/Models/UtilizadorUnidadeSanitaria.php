<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tenant\Traits\ForTenants;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UtilizadorUnidadeSanitaria
 * @package App\Models
 * @version March 30, 2020, 2:20 pm UTC
 *
 * @property \App\Models\UnidadeSanitaria unidade_sanitaria
 * @property \App\Models\Role role
 * @property string nome
 * @property integer unidade_sanitaria_id
 * @property string contacto
 * @property boolean activo
 * @property integer role_id
 * @property string nacionalidade
 * @property string observacoes
 */

class UtilizadorUnidadeSanitaria extends Model
{
    use SoftDeletes;
    // use ForTenants;

    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'nome',
        'email',
        'email_verificado',
        'unidade_sanitaria_id',
        'contacto',
        'user_id',
        'activo',
        'role_id',
        'nacionalidade',
        'observacoes'
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
        'unidade_sanitaria_id' => 'integer',
        'contacto' => 'string',
        'activo' => 'boolean',
        'role_id' => 'integer',
        'nacionalidade' => 'string',
        'observacoes' => 'string'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function unidadeSanitaria()
    {
        return $this->belongsTo(UnidadeSanitaria::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setEmailAttribute($value){
        $this->attributes['email'] = !empty($value) ? strtolower($value) : null;
    }

    public function getActivoAttribute(){
        if($this->attributes['activo'] == 1)
            return 1;
        return 0;
    }

    /**
     * Scope a query to only include UtilizadorClinica with Gestor Empresa role(codigo=6): Gestor Empresa
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByGestorUnidadeSanitaria($query)
    {
        return $query->whereHas('role', function ($q_role) {
            return $q_role->where('codigo', 6);
        });
    }


    public function scopeByUnidadeSanitaria($query, $unidade_sanitaria_id)
    {
        return $query->where('unidade_sanitaria_id', $unidade_sanitaria_id);
    }
}
