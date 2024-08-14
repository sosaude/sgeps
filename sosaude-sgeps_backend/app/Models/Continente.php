<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Continente extends Model
{
    public $timestamps = false;



    public $fillable = [
        'nome'
    ];

    protected $casts = [
        'nome' => 'string'
    ];

    public function paises()
    {
        return $this->hasMany(Pais::class);
    }
}
