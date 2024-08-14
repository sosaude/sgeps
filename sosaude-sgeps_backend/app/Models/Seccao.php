<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Seccao
 * @package App\Models
 * @version March 16, 2020, 8:47 am UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection roles
 * @property string nome
 */
class Seccao extends Model
{

    public $table = 'seccaos';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'nome'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function permissaos()
    {
        return $this->hasMany(Permissao::class);
    }
}
