<?php

namespace App\Models;

use App\Tenant\Traits\ForTenants;
use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UtilizadorClinica
 * @package App\Models
 * @version March 30, 2020, 2:20 pm UTC
 *
 * @property \App\Models\Clinica clinica
 * @property \App\Models\Role role
 * @property string nome
 * @property integer clinica_id
 * @property string contacto
 * @property boolean activo
 * @property integer role_id
 * @property string nacionalidade
 * @property string observacoes
 */
class UtilizadorClinica extends Model
{
    use SoftDeletes;
    use ForTenants;

    public $table = 'utilizador_clinicas';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'nome',
        'email',
        'clinica_id',
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
        'clinica_id' => 'integer',
        'contacto' => 'string',
        'activo' => 'boolean',
        'role_id' => 'integer',
        'nacionalidade' => 'string',
        'observacoes' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required',
        'email' => 'nullable|email|max:255',
        'clinica_id' => 'required',
        'role_id' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
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
    public function scopeByGestorClinica($query)
    {
        return $query->whereHas('role', function ($q_role) {
            return $q_role->where('codigo', 6);
        });
    }
}
