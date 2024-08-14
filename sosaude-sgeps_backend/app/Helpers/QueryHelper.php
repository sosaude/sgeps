<?php
namespace App\Helpers;

use App\Models\Tenant;

class QueryHelper
{
    // Tenant
    /**
     * @param string $attribute,
     * @param mixed $value
     *
     * @return int
     */
    public function tenantId($attribute, $value)
    {
        if ($attribute && $value) {
            return Tenant::where($attribute, $value)->pluck('id')->first();
        }

        return null;
    }
}
