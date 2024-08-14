<?php

namespace App\Models;

use Eloquent as Model;
use App\Models\EmpresaFarmaciaPivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Farmacia
 * @package App\Models
 * @version March 12, 2020, 1:54 pm UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection utilizadorFarmacias
 * @property string nome
 * @property string endereco
 * @property string horario_funcionamento
 * @property boolean activa
 * @property string contactos
 * @property string latitude
 * @property string longitude
 * @property string numero_alvara
 * @property string data_alvara_emissao
 * @property string observacoes
 */
class Farmacia extends Model
{
    // use SoftDeletes;

    public $table = 'farmacias';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'nome',
        'email',
        'endereco',
        'horario_funcionamento',
        'activa',
        'contactos',
        'latitude',
        'longitude',
        'numero_alvara',
        'data_alvara_emissao',
        'observacoes',
        'tenant_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
        'email' => 'string',
        'endereco' => 'string',
        'horario_funcionamento' => 'string',
        'activa' => 'boolean',
        'contactos' => 'string',
        'latitude' => 'string',
        'longitude' => 'string',
        'numero_alvara' => 'string',
        'data_alvara_emissao' => 'date',
        'observacoes' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'endereco' => 'required|string|max:255',
        'horario_funcionamento' => 'required',
        'activa' => 'required',
        'contactos' => 'required',
        'numero_alvara' => 'required',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function utilizadorFarmacias()
    {
        return $this->hasMany(\App\Models\UtilizadorFarmacia::class, 'farmacia_id');
    }

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class)
            ->using(EmpresaFarmaciaPivot::class);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = !empty($value) ? strtolower($value) : null;
    }

    public function setHorarioFuncionamentoAttribute($value)
    {
        $this->attributes['horario_funcionamento'] = json_encode($value);
    }

    public function setContactosAttribute($value)
    {
        $this->attributes['contactos'] = json_encode($value);
    }

    public function getHorarioFuncionamentoAttribute()
    {
        return json_decode($this->attributes['horario_funcionamento']);
    }

    public function getContactosAttribute()
    {
        return json_decode($this->attributes['contactos']);
    }

    public function getActivaAttribute()
    {
        if ($this->attributes['activa'] == 1)
            return 1;
        return 0;
    }

    public function farmaciaAssociadaAEmpresa($farmacia_id, $empresa_id)
    {
        return $this
            ->where('id', $farmacia_id)
            ->whereHas('empresas', function ($empresa) use ($empresa_id) {
                $empresa->where('empresa_id', $empresa_id);
            })
            ->first();
    }

    public function scopeEmails($query)
    {
        return $query
            ->where('email', '!=', null)
            ->where('email', '!=', '');
    }
}
