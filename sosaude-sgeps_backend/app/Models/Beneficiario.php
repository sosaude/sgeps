<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent as Model;
use App\Tenant\Traits\ForTenants;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Beneficiario
 * @package App\Models
 * @version May 20, 2020, 5:31 pm UTC
 *
 */
class Beneficiario extends Model
{
    // use SoftDeletes; 
    // use ForTenants;

    protected $dates = ['deleted_at'];



    public $fillable = [
        'activo',
        'nome',
        'numero_identificacao',
        'email',
        'numero_beneficiario',
        'endereco',
        'bairro',
        'telefone',
        'genero',
        'data_nascimento',
        'ocupacao',
        'aposentado',
        'tem_dependentes',
        'doenca_cronica',
        'doenca_cronica_nome',
        'empresa_id',
        'grupo_beneficiario_id',
        'user_id',
    ];

    public $hidden = ['user_id'];

    /**
     * The attributes thas should be appended
     * @var $appends
     */
    // public $appends = ['utilizador_activo'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'email' => 'string',
        'numero_identificacao' => 'string',
        'activo' => 'boolean',
        'numero_beneficiario' => 'string',
        'endereco' => 'string',
        'bairro' => 'string',
        'telefone' => 'string',
        'genero' => 'string',
        'data_nascimento' => 'date',
        'ocupacao' => 'string',
        'aposentado' => 'boolean',
        'tem_dependentes' => 'boolean',
        'doenca_cronica' => 'boolean',
        'doenca_cronica_nome' => 'string',
        'grupo_beneficiario_id' => 'integer',
        'user_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    /* public static $rules = [
        
    ]; */

    public function scopeByEmpresa($query, $empresa_id)
    {
        return $query->where('empresa_id', $empresa_id);
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

    public function grupoBeneficiario()
    {
        return $this->belongsTo(GrupoBeneficiario::class);
    }

    public function dependentes()
    {
        return $this->hasMany(DependenteBeneficiario::class);
    }

    public function baixasFarmacia()
    {
        return $this->hasMany(BaixaFarmacia::class);
    }

    public function pedidoReembolso()
    {
        return $this->hasMany(PedidoReembolso::class);
    }

    /* public function getUtilizadorActivoAttribute()
    {
        return $this->user->active;
        $this->unsetTelation('user');
    } */

    /* public function getAposentadoAttribute(){
        if($this->attributes['aposentado'] == true) {
            return 1;
        }else{
            return 0;
        }
    } */

    public function setEmailAttribute($value){
        $this->attributes['email'] = !empty($value) ? strtolower($value) : null;
    }
    
    public function setDoencaCronicaNomeAttribute($value){
        $this->attributes['doenca_cronica'] == 0 ? $this->attributes['doenca_cronica_nome'] = json_encode(array()) : $this->attributes['doenca_cronica_nome'] = json_encode($value);
    }

    public function getDoencaCronicaNomeAttribute()
    {
        return json_decode($this->attributes['doenca_cronica_nome']);
    }

    public function getDataNascimentoAttribute()
    {
        return $this->attributes['data_nascimento'] ? Carbon::create($this->attributes['data_nascimento'])->format('Y-m-d') : '';
    }

    
}
