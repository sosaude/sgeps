<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Cliente extends Authenticatable implements JWTSubject
{
    use Notifiable;
    
    public $fillable = [
        'logado_uma_vez',
        'nome',
        'numero_identificacao',
        'peso',
        'altura',
        'e_benefiairio_plano_saude',
        'tem_doenca_cronica',
        'doenca_cronica_nome',
        'tipo_sanguineo',
        'provincia',
        'cidade',
        'doenca_cronica',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'active',
        'beneficiario_id',
        'dependente_beneficiario_id'
    ];

    protected $hidden = ['password', 'email_verified_at', 'remember_token'];

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
        'logado_uma_vez' => 'boolean',
        'nome' => 'string',
        'numero_identificacao' => 'string',
        'peso' => 'float',
        'altura' => 'integer',
        'e_benefiairio_plano_saude' => 'boolean',
        'tem_doenca_cronica' => 'boolean',
        'doenca_cronica_nome' => 'array',
        'tipo_sanguineo' => 'string',
        'provincia' => 'string',
        'cidade' => 'string',
        'email' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'remember_token' => 'string',
        'active' => 'boolean',
        'beneficiario_id' => 'integer',
        'dependente_beneficiario_id' => 'integer'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /* public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    } */

    public function setDoencaCronicaNomeAttribute($value)
    {
        $this->attributes['doenca_cronica_nome'] = json_encode($value);
    }

    public function getDoencaCronicaNomeAttribute()
    {
        return json_decode($this->attributes['doenca_cronica_nome']);
    }

    public function sugestoes()
    {
        return $this->hasMany(Sugestao::class);
    }

    public function beneficiario()
    {
        return $this->belongsTo(Beneficiario::class);
    }

    public function dependenteBeneficiario()
    {
        return $this->belongsTo(DependenteBeneficiario::class);
    }

    public function getFotoPerfilAttribute()
    {
        /* $url = null;
        $foto_perfil = $this->attributes['foto_perfil'];
        if(!empty($foto_perfil)) {
            $url = env('APP_URL').'/storage'.$this->getUploadedFileDirectory().'/'.$this->attributes['foto_perfil'];
        }
        return $url; */

        $url = null;
        $foto_perfil = $this->attributes['foto_perfil'];
        if(!empty($foto_perfil)) {
            $url = $this->getUploadedFileDirectory().$this->attributes['foto_perfil'];
        }
        return $url;
    }

    public function getFotoDocumentoAttribute()
    {
        /* $url = null;
        $foto_documento = $this->attributes['foto_documento'];
        if(!empty($foto_documento)) {
            $url = env('APP_URL').'/storage'.$this->getUploadedFileDirectory().'/'.$this->attributes['foto_documento'];
        }
        return $url; */

        $url = null;
        $foto_documento = $this->attributes['foto_documento'];
        if(!empty($foto_documento)) {
            $url = $this->getUploadedFileDirectory().$this->attributes['foto_documento'];
        }
        return $url;
    }

    public function getUploadedFileDirectory()
    {
        // return "/clientes/" . $this->getKey();
        return aws_url().'/'.stogare_path_clientes().$this->getKey().'/';
    }

}
