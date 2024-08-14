<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CategoriaEmpresa
 * @package App\Models
 * @version March 23, 2020, 10:08 am UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection empresas
 * @property boolean codigo
 * @property string categoria
 */
class CategoriaEmpresa extends Model
{

    public $table = 'categoria_empresas';



    public $fillable = [
        'codigo',
        'nome'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'codigo' => 'boolean',
        'nome' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'codigo' => 'required',
        'nome' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function empresas()
    {
        return $this->hasMany(\App\Models\Empresa::class, 'categoria_empresa_id');
    }
}
