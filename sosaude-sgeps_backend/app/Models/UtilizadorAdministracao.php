<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class UtilizadorAdministracao
 * @package App\Models
 * @version March 25, 2020, 3:02 pm UTC
 *
 * @property \App\Models\Role role
 * @property \Illuminate\Database\Eloquent\Collection users
 * @property string nome
 * @property string contacto
 * @property string email
 * @property boolean activo
 * @property integer role_id
 */
class UtilizadorAdministracao extends Model
{
    use SoftDeletes;

    public $table = 'utilizador_administracaos';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'nome',
        'contacto',
        'email',
        'email_verificado',
        'activo',
        'role_id',
        'user_id',
        'administracao_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'nome' => 'string',
        'contacto' => 'string',
        'email' => 'string',
        'activo' => 'boolean',
        'role_id' => 'integer'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function administracao()
    {
        return $this->belongsTo(Administracao::class);
    }

    public function getActivoAttribute(){
        if($this->attributes['activo'] == 1)
            return 1;
        return 0;
    }


    public function scopeEmails($query)
    {
        return $query
            ->where('email', '!=', null)
            ->where('email', '!=', '')
            ;
    }
}
