<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaServico extends Model
{
    public $timestamps = false;

    public $fillable = [
        'nome',
        'codigo'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
        'codigo' => 'string',
        
    ];


    public function servicos()
    {
        return $this->hasMany(Servico::class);
    }

    public function categoriasServicoPlano()
    {
        return $this->hasMany(CategoriaServicoPlano::class);
    }

}
