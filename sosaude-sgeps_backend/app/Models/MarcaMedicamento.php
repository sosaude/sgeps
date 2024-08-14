<?php

namespace App\Models;

use Eloquent as Model;
use App\Models\FormaMarcaMedicamento;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MarcaMedicamento
 * @package App\Models
 * @version March 19, 2020, 7:01 am UTC
 *
 * @property \App\Models\Medicamento medicamento
 * @property string marca
 * @property integer medicamento_id
 * @property string codigo
 * @property string forma
 * @property string dosagem
 * @property string pais_origem
 */
class MarcaMedicamento extends Model
{
    use SoftDeletes;

    public $table = 'marca_medicamentos';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'marca',
        'medicamento_id',
        'codigo',
        // 'forma_marca_medicamento_id',
        // 'dosagem',
        'pais_origem'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'marca' => 'string',
        'medicamento_id' => 'integer',
        'codigo' => 'string',
        // 'forma_marca_medicamento_id' => 'integer',
        // 'dosagem' => 'string',
        'pais_origem' => 'string'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function medicamento()
    {
        return $this->belongsTo(\App\Models\Medicamento::class, 'medicamento_id', 'id');
    }

    /* public function formraMarcaMedicamento()
    {
        return $this->belongsTo(FormaMarcaMedicamento::class, 'forma_marca_medicamento_id');
    } */

    public function itensBaixaFarmacia()
    {
        return $this->hasMany(ItenBaixaFarmacia::class);
    }

    public function stockFarmacia()
    {
        return $this->hasMany(StockFarmacia::class);
    }
}
