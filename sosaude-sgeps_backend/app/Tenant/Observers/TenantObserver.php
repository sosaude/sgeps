<?php
namespace App\Tenant\Observers;

use App\Tenant\Manager\TenantManager;
use Illuminate\Database\Eloquent\Model;

class TenantObserver
{
    protected $tenant;

    public function __construct()
    {
        $this->tenant = app(TenantManager::class)->getTenant();
    }

    public function creating(Model $model)
    {
        if(!isset($model->tenant_id)) {
            $model->setAttribute('tenant_id', $this->tenant->id);
        }
    }
}