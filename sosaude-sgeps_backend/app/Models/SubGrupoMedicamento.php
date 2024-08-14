<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubGrupoMedicamento extends Model
{
    public $fillable = [
        'nome',
        'grupo_medicamento_id'
    ];
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
        'grupo_medicamento_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required|string|max:255|unique:sub_grupo_medicamentos,nome',
        'grupo_medicamento_id' => 'required|integer|exists:grupo_medicamentos,id'
    ];

    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class);
    }

    public function grupoMedicamentos()
    {
        return $this->belongsTo(GrupoMedicamento::class, 'grupo_medicamento_id');
    }

    public function subClassesMedicamentos()
    {
        return $this->hasMany(SubClasseMedicamento::class, 'sub_grupo_medicamento_id');
    }
}
