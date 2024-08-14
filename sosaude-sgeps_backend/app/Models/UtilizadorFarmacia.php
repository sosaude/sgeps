<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UtilizadorFarmacia
 * @package App\Models
 * @version March 16, 2020, 9:47 am UTC
 *
 * @property \App\Models\Farmacia farmacia
 * @property \Illuminate\Database\Eloquent\Collection users
 * @property string nome
 * @property integer farmacia_id
 * @property string contacto
 * @property integer numero_caderneta
 * @property string categoria_profissional
 * @property string Nacionalidade
 * @property string observacoes
 */
class UtilizadorFarmacia extends Model
{
    use SoftDeletes;

    public $table = 'utilizador_farmacias';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'nome',
        'numero_identificacao',
        'email',
        'email_verificado',
        'farmacia_id',
        'contacto',
        'numero_caderneta',
        'user_id',
        'activo',
        'role_id',
        'categoria_profissional',
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
        'numero_identificacao' => 'string',
        'email' => 'string',
        'farmacia_id' => 'integer',
        'contacto' => 'string',
        'numero_caderneta' => 'integer',
        'user_id' => 'integer',
        'activo' => 'boolean',
        'role_id' => 'integer',
        'categoria_profissional' => 'string',
        'nacionalidade' => 'string',
        'observacoes' => 'string'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function farmacia()
    {
        return $this->belongsTo(\App\Models\Farmacia::class, 'farmacia_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }

    public function setEmailAttribute($value){
        $this->attributes['email'] = !empty($value) ? strtolower($value) : null;
    }


    public function getActivoAttribute(){
        if($this->attributes['activo'] == 1)
            return 1;
        return 0;
    }



    public function scopeByFarmacia($query, $farmacia_id)
    {
        return $query->where('farmacia_id', $farmacia_id);
    }
}
