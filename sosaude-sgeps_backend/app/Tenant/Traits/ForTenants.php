<?php
namespace App\Tenant\Traits;

use App\Tenant\Manager\TenantManager;
use App\Tenant\Observers\TenantObserver;
use App\Tenant\Scopes\TenantScope;

trait ForTenants
{
    public static function boot()
    {
        parent::boot();

        $tenant = app(TenantManager::class)->getTenant();

        if ($tenant) {

            if ($tenant->id != 1) {

                static::addGlobalScope(new TenantScope($tenant));

                if ($tenant) {
                    static::observe(TenantObserver::class);
                }

            }

        }

    }
}
