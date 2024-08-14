<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicoUnidadeSanitaria extends Model
{
    public $fillable = [
        'preco',
        'iva',
        'preco_iva',
        'servico_id',
        'unidade_sanitaria_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'preco' => 'float',
        'iva' => 'integer',
        'preco_iva' => 'float',
        'servico_id' => 'integer',
        'unidade_sanitaria_id' => 'integer',
    ];

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function unidadeSanitaria()
    {
        return $this->belongsTo(UnidadeSanitaria::class);
    }

    public function scopeByUnidadeSanitaria($query, $unidade_sanitaria_id)
    {
        return $query->where('unidade_sanitaria_id', $unidade_sanitaria_id);
    }
}
