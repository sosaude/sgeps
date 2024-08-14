<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomeGenericoMedicamento extends Model
{
    public $fillable = [
        'nome'
    ];
    public $timestamps = false;

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
    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class, 'nome_generico_medicamento_id');
    }
}
