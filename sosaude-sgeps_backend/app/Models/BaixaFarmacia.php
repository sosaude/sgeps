<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaixaFarmacia extends Model
{
    //
    protected $fillable = [
        'valor',
        'responsavel',
        'beneficio_proprio_beneficiario',
        'beneficiario_id',
        'dependente_beneficiario_id',
        'farmacia_id',
        'empresa_id',
        'proveniencia',
        'comprovativo',
        'nr_comprovativo',
        'data_criacao_pedido_aprovacao',
        'data_aprovacao_pedido_aprovacao',
        'responsavel',
        'resposavel_aprovacao_pedido_aprovacao',
        'comentario_pedido_aprovacao',
        'comentario_baixa',
        'estado_baixa_id',
        
    ];

    protected $appends = ['tipo_proveniencia_texto'];

    public $estado_array = [
        'Aguardando confirmação' => 1,
        'Aguardando Pagamento' => 2,
        'Pagamento Processado' => 3
    ];

    public $tipo_proveniencia_array = [
        'Farmácia' => 1,
        'Unidade Sanitária' => 2
    ];

    protected $cast = [
        // 'comentario_pedido_aprovacao' => 'array',
        'valor' => 'float',
        'estado_baixa_id' => 'integer',
        'farmacia_id' => 'integer',
        'beneficio_proprio_beneficiario' => 'boolean',
        'beneficiario_id' => 'integer',
        'dependente_beneficiario_id' => 'integer',
        'proveniencia' => 'integer',
        'empresa_id' => 'integer',
        'nr_comprovativo' => 'string'
    ];

    public function setComentarioPedidoAprovacaoAttribute($value)
    {
        $this->attributes['comentario_pedido_aprovacao'] = json_encode($value);
    }

    public function setComentarioBaixaAttribute($value)
    {
        $this->attributes['comentario_baixa'] = json_encode($value);
    }

    public function setResponsavelAttribute($value) {
        $this->attributes['responsavel'] = json_encode($value);
    }

    public function getComentarioPedidoAprovacaoAttribute()
    {
        return json_decode($this->attributes['comentario_pedido_aprovacao']);
    }

    public function getComentarioBaixaAttribute()
    {
        return json_decode($this->attributes['comentario_baixa']);
    }

    public function getResponsavelAttribute()
    {
        return json_decode($this->attributes['responsavel']);
    }

    public function itensBaixaFarmacia()
    {
        return $this->hasMany(ItenBaixaFarmacia::class);
    }

    public function estadoBaixa()
    {
        return $this->belongsTo(EstadoBaixa::class);
    }

    public function beneficiario()
    {
        return $this->belongsTo(Beneficiario::class);
    }

    public function dependenteBeneficiario()
    {
        return $this->belongsTo(DependenteBeneficiario::class);
    }

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }


    /*  public function getEstadoAttribute()
    {
        return array_search($this->attributes['estado'], $this->estadoArray);
    } */
    public function setComprovativoAttribute($value)
    {
        $this->attributes['comprovativo'] = json_encode($value);
    }

    /* public function getEstadoTextoAttribute()
    {
        return array_search($this->attributes['estado'], $this->estado_array);
    } */

    public function getTipoProvenienciaTextoAttribute()
    {
        return array_search($this->attributes['proveniencia'], $this->tipo_proveniencia_array);
    }

    public function getComprovativoAttribute()
    {
        return json_decode($this->attributes['comprovativo']);
    }

    public function scopeByEmpresa($query, $empresa_id)
    {
        return $query->where('empresa_id', $empresa_id);
    }

    public function scopeByFarmacia($query, $farmacia_id)
    {
        return $query->where('farmacia_id', $farmacia_id);
    }

    public function scopeByEstado($query, $estado_codigo)
    {
        return $query->whereHas('estadoBaixa', function($estado_baixa) use ($estado_codigo) {
            return $estado_baixa->where('codigo', $estado_codigo);
        });
    }

    public function isEstado($estado)
    {
        return $this->estado_baixa_id == $estado;
    }
}
