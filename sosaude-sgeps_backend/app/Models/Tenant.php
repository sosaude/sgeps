<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    // use SoftDeletes;
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];
    
    //

    public function farmacia()
    {
        return $this->hasOne(Farmacia::class);
    }

    public function clinica()
    {
        return $this->hasOne(Clinica::class);
    }

    public function empresa()
    {
        return $this->hasOne(Empresa::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeByForeignKey($query, $attribute, $value)
    {
        if ($value) {
            return $query->where($attribute, $value);
        }

        return null;
    }
}
