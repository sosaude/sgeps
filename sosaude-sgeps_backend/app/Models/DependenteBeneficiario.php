<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent as Model;
use App\Models\Beneficiario;
use App\Tenant\Traits\ForTenants;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DependenteBeneficiario
 * @package App\Models
 * @version May 20, 2020, 5:45 pm UTC
 *
 */
class DependenteBeneficiario extends Model
{    
    // use ForTenants;

    public $fillable = [
        'activo',
        'nome',
        'numero_identificacao',
        'email',
        'parantesco',
        'endereco',
        'bairro',
        'telefone',
        'genero',
        'data_nascimento',
        'doenca_cronica',
        'doenca_cronica_nome',
        'beneficiario_id',
        'user_id',
        'empresa_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'activo' => 'boolean',
        'nome' => 'string',
        'numero_identificacao' => 'string',
        'email' => 'string',
        'parantesco' => 'string',
        'activo' => 'boolean',
        'endereco' => 'string',
        'bairro' => 'string',
        'telefone' => 'string',
        'genero' => 'string',
        'data_nascimento' => 'date',
        'doenca_cronica' => 'boolean',
        'doenca_cronica_nome' => 'string',
        'beneficiario_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function scopeByEmpresa($query, $empresa_id)
    {
        return $query->where('empresa_id', $empresa_id);
    }
    
    public function beneficiario()
    {
        return $this->belongsTo(Beneficiario::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->hasOne(Cliente::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function setEmailAttribute($value){
        $this->attributes['email'] = strtolower($value);
    }

    public function setDoencaCronicaNomeAttribute($value)
    {
        $this->attributes['doenca_cronica_nome'] = json_encode($value);
    }

    public function getDoencaCronicaNomeAttribute()
    {
        return json_decode($this->attributes['doenca_cronica_nome']);
    }

    public function getDataNascimentoAttribute()
    {
        return $this->attributes['data_nascimento'] ? Carbon::create($this->attributes['data_nascimento'])->format('Y-m-d') : '';
    }

    public function pedidoReembolso()
    {
        return $this->hasMany(PedidoReembolso::class);
    }

    
}
