<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    public $timestamps = false;



    public $fillable = [
        'nome',
        'codigo',
        'continente_id'
    ];

    protected $casts = [
        'nome' => 'string',
        'codigo' => 'string',
        'continente_id' => 'integer'
    ];

    public function continente()
    {
        return $this->belongsTo(Continente::class);
    }
}
