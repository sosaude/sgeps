<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Medicamento
 * @package App\Models
 * @version March 19, 2020, 5:38 am UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection marcaMedicamentos
 * @property string nome
 * @property string codigo
 */
class Medicamento extends Model
{
    use SoftDeletes;

    public $table = 'medicamentos';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];
    // protected $hidden = ['pivot'];


    public $fillable = [
        'codigo',
        'dosagem',
        'nome_generico_medicamento_id',
        'forma_medicamento_id',
        'grupo_medicamento_id',
        'sub_grupo_medicamento_id',
        'sub_classe_medicamento_id'
    ];

    

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
        'codigo' => 'string',
        'dosagem' => 'string',
        'nome_generico_medicamento_id' => 'integer',
        'forma_medicamento_id' => 'integer',
        'grupo_medicamento_id' => 'integer',
        'sub_grupo_medicamento_id' => 'integer',
        'sub_classe_medicamento_id' => 'integer'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function nomeGenerico()
    {
        return $this->belongsTo(NomeGenericoMedicamento::class, 'nome_generico_medicamento_id');
    }

    public function marcaMedicamentos()
    {
        return $this->hasMany(\App\Models\MarcaMedicamento::class, 'medicamento_id', 'id');
    }

    public function formaMedicamento()
    {
        return $this->belongsTo(FormaMedicamento::class);
    }

    public function grupoMedicamento()
    {
        return $this->belongsTo(GrupoMedicamento::class); 
    }

    public function subGrupoMedicamento()
    {
        return $this->belongsTo(SubGrupoMedicamento::class); 
    }

    public function subClasseMedicamento()
    {
        return $this->belongsTo(SubClasseMedicamento::class); 
    }

    public function gruposMedicamentoPlano()
    {
        return $this->belongsToMany(GrupoMedicamentoPlano::class, 'grupo_med_pla_med', 'medicamento_id', 'grupo_medicamento_plano_id')
        ->using(GrupoMedicamentoPlanoMedicamentoPivot::class)
        ->withPivot('coberto', 'pre_autorizacao');
    }


    public function itensBaixa()
    {
        return $this->hasMany(ItenBaixaFarmacia::class);
    }


    public function stockFarmacia()
    {
        return $this->hasMany(StockFarmacia::class);
    }
}
