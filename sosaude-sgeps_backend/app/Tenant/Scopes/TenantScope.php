<?php
namespace App\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    protected $tenant;

    public function __construct($tenant)
    {
        $this->tenant = $tenant;
    }

    public function apply(Builder $builder, Model $model)
    {
        if($this->tenant)
            return $builder->where('tenant_id', $this->tenant->id);

        return $builder->where('tenant_id', 0);
    }
}