<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoMedicamento extends Model
{
    public $fillable = [
        'nome'
    ];
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required|string|255|unique:grupo_medicamentos,nome'
    ];

    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class);
    }

    public function subGruposMedicamentos()
    {
        return $this->hasMany(SubGrupoMedicamento::class, 'grupo_medicamento_id');
    }

    public function gruposMedicamentoPlano()
    {
        return $this->hasMany(GrupoMedicamento::class);
    }

    public function medicamentosThrough() {
        return $this->hasManyThrough(Medicamento::class, SubGrupoMedicamento::class);
    }
}
