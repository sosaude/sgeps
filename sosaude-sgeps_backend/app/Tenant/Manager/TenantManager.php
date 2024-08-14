<?php
namespace App\Tenant\Manager;

use Illuminate\Support\Facades\Auth;

class TenantManager
{
    public function getTenant()
    {
        if(!Auth::user()){
            return null;
        }
        
        $tenant = auth()->user()->tenant()->first();

        return $tenant;
    }
}