<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoMedicamentoPlano extends Model
{
    public $timestamps = false;



    public $fillable = [
        'comparticipacao_factura',
        'beneficio_ilimitado',
        'valor_comparticipacao_factura',
        'valor_beneficio_limitado',
        'plano_saude_id',
        'grupo_medicamento_id'
    ];

    protected $casts = [
        'comparticipacao_factura' => 'boolean',
        'beneficio_ilimitado' => 'boolean',
        'valor_comparticipacao_factura' => 'double',
        'valor_beneficio_limitado' => 'double',
        'plano_saude_id' => 'integer',
        'grupo_medicamento_id' => 'integer'
    ];

    public function planoSaude()
    {
        return $this->belongsTo(PlanoSaude::class);
    }

    public function medicamentos()
    {
        return $this->belongsToMany(Medicamento::class, 'grupo_med_pla_med', 'grupo_medicamento_plano_id', 'medicamento_id')
        ->using(GrupoMedicamentoPlanoMedicamentoPivot::class)
        ->withPivot('coberto', 'pre_autorizacao');
    }

    public function grupoMedicamento()
    {
        return $this->belongsTo(GrupoMedicamento::class);
    }

    public function scopeByPlanoSaude($query, $plano_saude_id)
    {
        return $query->where('plano_saude_id', $plano_saude_id);
    }
}
