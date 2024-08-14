<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoPedidoReembolso extends Model
{
    


    public $fillable = [
        'nome',
        'codigo',
    ];

    protected $casts = [
        'nome' => 'string',
        'codigo' => 'string',
    ];

    public function pedidosReembolso()
    {
        return $this->hasMany(PedidoReembolso::class);
    }

    /* public function baixasUnidadesSanitarias()
    {
        return $this->hasMany(BaixaUnidadeSanitaria::class);
    } */
}
