<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UtilizadorUnidadeSanitaria;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Clinica
 * @package App\Models
 * @version March 30, 2020, 2:10 pm UTC
 *
 * @property string nome
 * @property string endereco
 * @property string email
 * @property string contactos
 * @property integer nuit
 * @property string latitude
 * @property string longitude
 */

class UnidadeSanitaria extends Model
{
    // use SoftDeletes;

    protected $dates = ['deleted_at'];

    public $fillable = [
        'categoria_unidade_sanitaria_id',
        'nome',
        'endereco',
        'email',
        'contactos',
        'nuit',
        'latitude',
        'longitude',
        'tenant_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'categoria_unidade_sanitaria_id' => 'integer',
        'nome' => 'string',
        'endereco' => 'string',
        'email' => 'string',
        'contactos' => 'string',
        'nuit' => 'integer',
        'latitude' => 'string',
        'longitude' => 'string',
    ];

    

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categoriaUnidadeSanitaria()
    {
        return $this->belongsTo(CategoriaUnidadeSanitaria::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function utilizadoresUnidadeSanitaria()
    {
        return $this->hasMany(UtilizadorUnidadeSanitaria::class);
    }

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class);
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

    public function unidadeSanitariaAssociadaAEmpresa($unidade_sanitaria_id, $empresa_id)
    {
        return $this
            ->where('id', $unidade_sanitaria_id)
            ->whereHas('empresas', function ($empresa) use ($empresa_id) {
                $empresa->where('empresa_id', $empresa_id);
            })
            ->first();
    }

    public function scopeEmails($query)
    {
        return $query
            ->where('email', '!=', null)
            ->where('email', '!=', '')
            ;
    }

}
