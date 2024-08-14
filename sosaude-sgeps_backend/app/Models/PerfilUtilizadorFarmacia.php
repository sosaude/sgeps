<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PerfilUtilizadorFarmacia
 * @package App\Models
 * @version March 19, 2020, 2:30 pm UTC
 *
 * @property string perfil
 * @property integer codigo
 */
class PerfilUtilizadorFarmacia extends Model
{
    use SoftDeletes;

    public $table = 'perfil_utilizador_farmacias';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'perfil',
        'codigo'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'perfil' => 'string',
        'codigo' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'perfil' => 'required',
        'codigo' => 'required'
    ];

    
}
