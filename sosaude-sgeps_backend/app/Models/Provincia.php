<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    //
    public $timestamps = false;

    protected $fillable = ['codigo', 'nome'];
    
    /* public function cientes()
    {
        return $this->hasMany(Cliente::class);
    } */
}
