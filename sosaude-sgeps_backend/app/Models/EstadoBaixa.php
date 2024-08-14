<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoBaixa extends Model
{
    public $timestamps = false;



    public $fillable = [
        'referencia',
        'nome',
        'codigo',
    ];

    protected $casts = [
        'referencia',
        'nome' => 'string',
        'codigo' => 'string',
    ];

    public function baixasFarmacias()
    {
        return $this->hasMany(BaixaFarmacia::class);
    }

    public function baixasUnidadesSanitarias()
    {
        return $this->hasMany(BaixaUnidadeSanitaria::class);
    }
}
