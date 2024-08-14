<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Servico
 * @package App\Models
 * @version April 16, 2020, 9:34 am UTC
 *
 * @property string nome
 */
class Servico extends Model
{
    public $timestamps = false;

    public $fillable = [
        'nome',
        'categoria_servico_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
        'categoria_servico_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required',
        'categoria_servico_id' => 'integer'
    ];

    public function categoriaServico()
    {
        return $this->belongsTo(CategoriaServico::class);
    }

    public function categoriaServicoPlano()
    {
        return $this->belongsToMany(CategoriaServicoPlano::class, 'categoria_serv_pla_serv', 'servico_id', 'categoria_servico_plano_id')
        ->using(CategoriaServicoPlanoServicoPivot::class)
        ->withPivot('coberto', 'pre_autorizacao');
    }

    
}
