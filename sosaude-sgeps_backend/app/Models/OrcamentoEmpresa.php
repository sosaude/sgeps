<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent as Model;
use App\Tenant\Traits\ForTenants;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class OrcamentoEmpresa
 * @package App\Models
 * @version March 14, 2022, 12:31 pm UTC
 *
 */
class OrcamentoEmpresa extends Model
{
    use SoftDeletes;

    public $table = 'orcamento_empresas';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    protected $dates = ['deleted_at'];

    public $fillable = [
        'tipo_orcamento',
        'orcamento_laboratorio',
        'orcamento_farmacia',
        'orcamento_clinica',
        'executado',
        'ano_de_referencia',
        'empresa_id',
    ];
    
    /**
     * The attributes thas should be appended
     * @var $appends
     */

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tipo_orcamento' => 'string',
        'orcamento_laboratorio' => 'double',
        'orcamento_farmacia' => 'double',
        'orcamento_clinica' => 'double',
        'executado' => 'boolean',
        'ano_de_referencia' => 'string',
        'empresa_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    /* public static $rules = [
        
    ]; */

    public function scopeByEmpresa($query, $empresa_id)
    {
        return $query->where('empresa_id', $empresa_id);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

}
