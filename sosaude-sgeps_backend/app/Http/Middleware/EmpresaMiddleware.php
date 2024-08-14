<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EmpresaMiddleware
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

        $empresa_id = Auth::user()->userEmpresaId();
        if (empty($empresa_id)) {
            return response()->json('Empresa do usuário não encontrada!', 404);
        }
        $request['empresa_id'] = $empresa_id;

        return $next($request);
    }
}
