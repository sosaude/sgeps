<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MarcaMedicamento;

class StockFarmacia extends Model
{
    public $fillable = [
        'medicamento_id',
        'marca_medicamento_id',
        'preco',
        'iva',
        'preco_iva',
        'quantidade_disponivel',
        'farmacia_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'medicamento_id' => 'integer',
        'marca_medicamento_id' => 'integer',
        'preco' => 'float',
        'iva' => 'integer',
        'preco_iva' => 'float',
        'quantidade_disponivel' => 'integer',
        'farmacia_id' => 'integer'
    ];

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function marcaMedicamento()
    {
        return $this->belongsTo(MarcaMedicamento::class);
    }

    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class);
    }

    public function scopeByFarmacia($query, $farmacia_id)
    {
        return $query->where('farmacia_id', $farmacia_id);
    }
}
