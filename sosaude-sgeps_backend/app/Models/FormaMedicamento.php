<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class FormaMarcaMedicamento
 * @package App\Models
 * @version April 15, 2020, 9:22 pm UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection marcaMedicamentos
 * @property string forma
 */
class FormaMedicamento extends Model
{

    



    public $fillable = [
        'forma'
    ];
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'forma' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'forma' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class);
    }
}
