<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GastoReembolso extends Model
{
    public $fillable = [
        'nome',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
    ];
}
