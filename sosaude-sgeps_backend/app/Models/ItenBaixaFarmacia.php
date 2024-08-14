<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItenBaixaFarmacia extends Model
{
    //

    protected $fillable = [
        'preco', 
        'iva', 
        'preco_iva', 
        'quantidade', 
        'baixa_farmacia_id', 
        'marca_medicamento_id'
    ];

    protected $cast = [
        'preco' => 'float',
        'iva' => 'integer',
        'preco_iva' => 'float',
        'quantidade' => 'quantidade',
    ];

    public function baixaFarmacia()
    {
        return $this->belongsTo(BaixaFarmacia::class);
    }

    public function marcaMedicamento()
    {
        return $this->belongsTo(MarcaMedicamento::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}
