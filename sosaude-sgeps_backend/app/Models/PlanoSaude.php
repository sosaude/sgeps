<?php

namespace App\Models;

use App\Tenant\Traits\ForTenants;
use Illuminate\Database\Eloquent\Model;

class PlanoSaude extends Model
{
    // use ForTenants;

    public $fillable = [
        'beneficio_anual_segurando_limitado',
        'valor_limite_anual_segurando',
        'limite_fora_area_cobertura',
        'valor_limite_fora_area_cobertura',
        'regiao_cobertura',
        'grupo_beneficiario_id',
        'empresa_id'
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'beneficio_anual_segurando_limitado' => 'boolean',
        'valor_limite_anual_segurando' => 'double',
        'limite_fora_area_cobertura' => 'boolean',
        'valor_limite_fora_area_cobertura' => 'double',
        'regiao_cobertura' => 'string',
        'grupo_beneficiario_id' => 'integer',
        'empresa_id' => 'integer'
    ];

    public function grupoBeneficiario()
    {
        return $this->belongsTo(GrupoBeneficiario::class);
    }

    public function setRegiaoCoberturaAttribute($value)
    {
        $this->attributes['regiao_cobertura'] = json_encode($value);
    }

    public function getRegiaoCoberturaAttribute()
    {
        $regiao_cobertura_array = json_decode($this->attributes['regiao_cobertura']);
        if (is_array($regiao_cobertura_array)) {
            $regiao_cobertura_collection = Pais::with('continente')
                ->whereIn('id', $regiao_cobertura_array)
                ->get()
                ->map(function ($regiao) {
                    return
                        [
                            'id' => $regiao->id,
                            'codigo' => $regiao->codigo,
                            'nome' => $regiao->nome,
                            'continente_id' => $regiao->continente->id,
                            'continente_nome' => $regiao->continente->nome
                        ];
                });
            // return json_decode($this->attributes['regiao_cobertura']);
            return $regiao_cobertura_collection;
        }

        return null;
    }

    public function gruposMedicamentoPlano()
    {
        return $this->hasMany(GrupoMedicamentoPlano::class);
    }

    public function categoriasServicoPlano()
    {
        return $this->hasMany(CategoriaServicoPlano::class);
    }

    public function scopeByEmpresa($query, $empresa_id)
    {
        return $query->where('empresa_id', $empresa_id);
    }
}
