<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaServicoPlano extends Model
{
    public $timestamps = false;
    public $fillable = [
        
        'beneficio_ilimitado',
        'valor_beneficio_limitado',
        'sujeito_limite_global',
        'comparticipacao_factura',
        'valor_comparticipacao_factura',        
        'plano_saude_id',
        'categoria_servico_id'
    ];

    protected $casts = [
        
        'beneficio_ilimitado' => 'boolean',
        'valor_beneficio_limitado' => 'double',
        'sujeito_limite_global' => 'double',
        'comparticipacao_factura' => 'boolean',
        'valor_comparticipacao_factura' => 'double',        
        'plano_saude_id' => 'integer',
        'categoria_servico_id' => 'integer'
    ];

    public function planoSaude()
    {
        return $this->belongsTo(PlanoSaude::class);
    }

    public function categoriaServico()
    {
        return $this->belongsTo(CategoriaServico::class);
    }

    public function servicos()
    {
        return $this->belongsToMany(Servico::class, 'categoria_serv_pla_serv', 'categoria_servico_plano_id', 'servico_id')
        ->using(CategoriaServicoPlanoServicoPivot::class)
        ->withPivot('coberto', 'pre_autorizacao');
    }


    public function scopeByPlanoSaude($query, $plano_saude_id)
    {
        return $query->where('plano_saude_id', $plano_saude_id);
    }
}
