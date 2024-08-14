<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissao extends Model
{
    protected $hidden = ['pivot'];
    
    public $fillable = ['nome', 'seccao_id'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function seccao()
    {
        return $this->belongsTo(Seccao::class);
    }

    public function scopeBySeccaoEmpresa()
    {
        return $this->whereHas('seccao', function($query) {
            return $query->where('code', 2);
        });
    }

    public function scopeBySeccaoFarmacia()
    {
        return $this->whereHas('seccao', function($query) {
            return $query->where('code', 3);
        });
    }

    public function scopeBySeccaoUnidadeSanitaria()
    {
        return $this->whereHas('seccao', function($query) {
            return $query->where('code', 4);
        });
    }
}
