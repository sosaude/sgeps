<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItenBaixaUnidadeSanitaria extends Model
{
    protected $fillable = [
        'quantidade', 
        'preco', 
        'iva', 
        'preco_iva', 
        'baixa_unidade_sanitaria_id', 
        'servico_id'
    ];

    protected $cast = [
        'preco' => 'float',
        'iva' => 'integer',
        'preco_iva' => 'float',
        'quantidade' => 'quantidade',
    ];

    public function baixaUnidadeSanitaria()
    {
        return $this->belongsTo(BaixaUnidadeSanitaria::class);
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }
}
