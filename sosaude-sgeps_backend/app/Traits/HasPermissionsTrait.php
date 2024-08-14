<?php
namespace App\Traits;

use App\Models\Permissao;

trait HasPermissionsTrait
{
    public function permissaos()
    {
        return $this->belongsToMany(Permissao::class);
    }

    public function hasPermissionTo($permissao)
    {
        // dd($this->permissaos);
        return $this->hasPermission($permissao);
    }

    protected function hasPermission($permissao)
    {        
        return (bool) $this->permissaos()->where('nome', $permissao->nome)->count();
    }

    
}