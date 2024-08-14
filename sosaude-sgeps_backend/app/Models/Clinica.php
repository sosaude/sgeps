<?php

namespace App\Models;

use Eloquent as Model;
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
class Clinica extends Model
{
    use SoftDeletes;

    public $table = 'clinicas';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
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
        'nome' => 'string',
        'endereco' => 'string',
        'email' => 'string',
        'contactos' => 'string',
        'nuit' => 'integer',
        'latitude' => 'integer',
        'longitude' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required',
        'endereco' => 'required',
        'nuit' => 'required',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function utilizadoresClinica()
    {
        return $this->hasMany(\App\Models\UtilizadorClinica::class, 'clinica_id');
    }

    public function empresas()
    {
        return $this->belongsToMany(Empresa::class);
    }
    
    public function setContactosAttribute($value){
        $this->attributes['contactos'] = json_encode($value);
    }
    
    public function getContactosAttribute(){
        return json_decode($this->attributes['contactos']);
    }

    
}
