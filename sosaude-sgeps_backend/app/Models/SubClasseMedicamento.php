<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubClasseMedicamento extends Model
{
    public $fillable = [
        'nome',
        'sub_grupo_medicamento_id'
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
        'sub_grupo_medicamento_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required|string|max:255|unique:sub_classe_medicamentos,nome',
        'sub_grupo_medicamento_id' => 'required|integer|exists:sub_grupo_medicamentos,id'
    ];

    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class);
    }

    public function subGrupoMedicamentos()
    {
        return $this->belongsTo(SubGrupoMedicamento::class, 'sub_grupo_medicamento_id');
    }
}
