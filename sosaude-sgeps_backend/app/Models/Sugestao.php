<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Sugestao
 * @package App\Models
 * @version March 26, 2020, 1:32 pm UTC
 *
 * @property \App\Models\User user
 * @property string conteudo
 * @property integer user_id
 */
class Sugestao extends Model
{

    public $table = 'sugestaos';
    // public $timestamps = false;

    public $fillable = [
        'conteudo',
        'user_id',
        'cliente_id',
        'created_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'conteudo' => 'string',
        'user_id' => 'integer',
        'cliente_id' => 'integer',
    ];

    // Append these atributes to a collection
    protected $appends = [
        'contacto',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'conteudo' => 'required',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function utilizadorFarmacia()
    {
        return $this->hasOne(UtilizadorFarmacia::class);
    }

    public function utilizadorUnidadeSanitaria()
    {
        return $this->hasOne(UtilizadorUnidadeSanitaria::class);
    }

    public function utilizadorEmpresa()
    {
        return $this->hasOne(UtilizadorEmpresa::class);
    }

    public function utilizadorAdministracao()
    {
        return $this->hasOne(UtilizadorAdministracao::class);
    }

    /* public function sugestoes()
    {
        if ($this->utilizador_farmacia_id == null) {
            return $this->with('user.role');
            // $this->novo_campo = "Diferente de null";
            // dd("Diferente de null");
        } else {
            dd("Igual a null");
            // $this->novo_campo = "Igual a null";
        }
    } */

    public function getContactoAttribute()
    {
        // return $this->attributes['contacto'] = $this->user->utilizador_farmacia_id;

        if(!empty($this->user)) {
            if($this->user->utilizadorFarmacia != null){
                return $this->attributes['contacto'] = $this->user->utilizadorFarmacia->contacto;
            }
            if($this->user->utilizadorUnidadeSanitaria != null){
                return $this->attributes['contacto'] = $this->user->utilizadorUnidadeSanitaria->contacto;
            }
            if($this->user->utilizadorEmpresa != null){
                return $this->attributes['contacto'] = $this->user->utilizadorEmpresa->contacto;
            }
            if($this->user->utilizadorAdministracao != null){
                return $this->attributes['contacto'] = $this->user->utilizadorAdministracao->contacto;
            }
        }
        
        if(!empty($this->cliente)){
            return $this->attributes['contacto'] = '';
        } else {
            return $this->attributes['contacto'] = '';
        }
        
        
    }
}
