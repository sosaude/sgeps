<?php

namespace App\Models;

use Eloquent as Model;
use App\Models\EmpresaFarmaciaPivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Empresa
 * @package App\Models
 * @version March 23, 2020, 10:08 am UTC
 *
 * @property \App\Models\CategoriaEmpresa categoriaEmpresa
 * @property \Illuminate\Database\Eloquent\Collection utilizadorEmpresas
 * @property string nome
 * @property integer categoria_empresa_id
 * @property string endereco
 * @property string email
 * @property string nuit
 * @property string contactos
 * @property string delegacao
 */
class Empresa extends Model
{
    // use SoftDeletes;

    public $table = 'empresas';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];
    


    public $fillable = [
        'nome',
        'categoria_empresa_id',
        'endereco',
        'email',
        'nuit',
        'contactos',
        'delegacao',
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
        'categoria_empresa_id' => 'integer',
        'endereco' => 'string',
        'email' => 'string',
        'nuit' => 'string',
        'contactos' => 'string',
        'delegacao' => 'string'
    ];

    

    /* public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    } */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function categoriaEmpresa()
    {
        return $this->belongsTo(\App\Models\CategoriaEmpresa::class, 'categoria_empresa_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function utilizadoresEmpresas()
    {
        return $this->hasMany(\App\Models\UtilizadorEmpresa::class, 'empresa_id');
    }

    public function beneficiarios()
    {
        return $this->hasMany(Beneficiario::class);
    }

    public function dependentesBeneficiario()
    {
        return $this->hasMany(Beneficiario::class);
    }

    /* public function farmacias()
    {
        return $this->belongsToMany(Farmacia::class);
    } */

    public function farmacias()
    {
        return $this->belongsToMany(Farmacia::class)
        ->using(EmpresaFarmaciaPivot::class);
    }

    public function clinicas()
    {
        return $this->belongsToMany(Clinica::class);
    }

    public function unidadesSanitarias()
    {
        return $this->belongsToMany(UnidadeSanitaria::class);
    }

    public function baixaFarmacia()
    {
        return $this->hasMany(BaixaFarmacia::class);
    }

    public function baixaUnidadeSanitaria()
    {
        return $this->hasMany(BaixaUnidadeSanitaria::class);
    }

    public function pedidoReembolso()
    {
        return $this->hasMany(PedidoReembolso::class);
    }

    public function setContactosAttribute($value){
        $this->attributes['contactos'] = json_encode($value);
    }

    public function setNuitAttribute($value){
        $this->attributes['nuit'] = !empty($value) ? $value : null;
    }

    public function setEmailAttribute($value){
        $this->attributes['email'] = !empty($value) ? strtolower($value) : null;
    }
    
    public function getContactosAttribute(){
        return json_decode($this->attributes['contactos']);
    }

    public function scopeEmails($query)
    {
        return $query
            ->where('email', '!=', null)
            ->where('email', '!=', '')
            ;
    }

    
}
