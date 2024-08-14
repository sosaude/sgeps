<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoencaCronica extends Model
{
    public $timestamps = false;
    
    public $fillable = [
        'nome'
    ];

    protected $casts = [
        'nome' => 'string'
    ];
}
