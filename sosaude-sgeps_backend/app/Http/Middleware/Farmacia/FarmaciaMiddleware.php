<?php

namespace App\Http\Middleware\Farmacia;

use Closure;
use Illuminate\Support\Facades\Auth;

class FarmaciaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $farmacia_id = Auth::user()->userFarmaciaId();
        if (empty($farmacia_id)) {
            return response()->json('Não possui permissões para aceder a esta Secção!', 403);
        }

        $request['farmacia_id'] = $farmacia_id;
        return $next($request);
    }
}
