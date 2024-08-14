<?php

namespace App\Models;

use App\Models\Role;
use App\Models\Sugestao;
use App\Models\UtilizadorAdministracao;
use App\Models\UtilizadorClinica;
use App\Models\UtilizadorEmpresa;
use App\Models\UtilizadorFarmacia;
use App\Traits\HasPermissionsTrait;
use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User
 * @package App\Models
 * @version March 11, 2020, 1:25 pm UTC
 *
 * @property string nome
 * @property string email
 * @property string|\Carbon\Carbon email_verified_at
 * @property string codigo_login
 * @property string password
 * @property string remember_token
 * @property boolean active
 * @property integer role_id
 * @property integer utilizador_farmacia_id
 * @property integer utilizador_empresa_id
 */
class User extends Authenticatable implements JWTSubject
{
    use SoftDeletes;
    use Notifiable;
    use HasPermissionsTrait;

    public $table = 'users';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

    protected $hidden = ['password', 'email_verified_at', 'remember_token', 'role_id', 'created_at', 'updated_at', 'pivot'];

    // protected $appends = ['organizacao_nome'];

    public $fillable = [
        'nome',
        'email',
        'email_verified_at',
        'codigo_login',
        'password',
        'remember_token',
        'active',
        'disbled_login_by_wrong_pass',
        'sent_disabled_login',
        'loged_once',
        'login_attempts',
        'role_id',
        'utilizador_farmacia_id',
        'utilizador_empresa_id',
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
        'email_verified_at' => 'datetime',
        'codigo_login' => 'string',
        'password' => 'string',
        'remember_token' => 'string',
        'active' => 'boolean',
        'disbled_login_by_wrong_pass' => 'boolean',
        'sent_disabled_login' => 'boolean',
        'loged_once' => 'boolean',
        'login_attempts' => 'integer',
        'role_id' => 'integer',
        'utilizador_farmacia_id' => 'integer',
        'utilizador_empresa_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'nome' => 'required',
        'password' => 'required',
        'role_id' => 'required',
    ];

    // Rest omitted for brevity

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

    /**
     * Checks if the user has role, Return true or false.
     *
     * @return boolean
     */
    public function hasRole($roles)
    {
        foreach ($roles as $role) {
            if ($this->role->codigo == $role) {
                return true;
            }
        }

        return false;
    }

    public function utilizadorFarmacia()
    {
        return $this->hasOne(UtilizadorFarmacia::class);
    }

    public function utilizadorClinica()
    {
        return $this->hasOne(UtilizadorClinica::class);
    }

    public function utilizadorUnidadeSanitaria()
    {
        return $this->hasOne(UtilizadorUnidadeSanitaria::class);
    }

    public function utilizadorEmpresa()
    {
        return $this->hasOne(UtilizadorEmpresa::class);
    }

    public function utilizadorAdministracao()
    {
        return $this->hasOne(UtilizadorAdministracao::class);
    }

    public function beneficiario()
    {
        return $this->hasOne(Beneficiario::class);
    }

    public function dependenteBeneficiario()
    {
        return $this->hasOne(DependenteBeneficiario::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function sugestoes()
    {
        return $this->hasMany(Sugestao::class, 'user_id');
    }

    public function scopeAdmins($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('codigo', '1');
        });
    }

    public function scopeAdminsEmails($query)
    {
        return $query
            ->where('email', '!=', null)
            ->where('email', '!=', '')
            ->whereHas('role', function ($q) {
                $q->where('codigo', '1');
            });
    }

    public function userEmpresaId()
    {

        /* if ($seccao = $this->role->seccao) {

            if ($seccao->code == 2) {
                $empresa_id = $this->tenant->empresa_id;

                return $empresa_id;
            }

            
        }

        return null; */

        if ($seccao = $this->role->seccao) {

            if ($seccao->code == 2) {

                if (!empty($this->utilizadorEmpresa)) {

                    $empresa_id = $this->utilizadorEmpresa->empresa_id;

                    return $empresa_id;
                }
            }
        }

        return null;
    }

    public function userFarmaciaId()
    {
        if ($seccao = $this->role->seccao) {

            if ($seccao->code == 3) {
                if (!empty($this->utilizadorFarmacia)) {

                    $farmacia_id = $this->utilizadorFarmacia->farmacia_id;

                    return $farmacia_id;
                }
            }
        }

        return null;
    }

    public function userUnidadeSanitariaId()
    {
        if ($seccao = $this->role->seccao) {

            if ($seccao->code == 4) {

                if (!empty($this->utilizadorUnidadeSanitaria)) {

                    $unidade_sanitaria_id = $this->utilizadorUnidadeSanitaria->unidade_sanitaria_id;

                    return $unidade_sanitaria_id;
                }
            }
        }

        return null;
    }

    public function utilizadorEntidade()
    {
        
        if (!empty($this->utilizadorAdministracao)) {
            return $this->utilizadorAdministracao;
        } elseif (!empty($this->utilizadorFarmacia)) {
            return $this->utilizadorFarmacia;
        } elseif (!empty($this->utilizadorUnidadeSanitaria)) {
            return $this->utilizadorUnidadeSanitaria;
        } elseif (!empty($this->utilizadorEmpresa)) {
            return $this->utilizadorEmpresa;
        } else {
            return null;
        }
    }
}
