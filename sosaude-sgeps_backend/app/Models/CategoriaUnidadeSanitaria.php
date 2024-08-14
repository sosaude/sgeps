<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaUnidadeSanitaria extends Model
{
    //
    public $fillable = ['nome'];

    public function unidadesSanitarias()
    {
        return $this->hasMany(UnidadeSanitaria::class);
    }
}
