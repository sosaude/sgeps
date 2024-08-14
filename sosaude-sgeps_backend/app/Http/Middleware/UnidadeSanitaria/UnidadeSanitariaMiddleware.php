<?php

namespace App\Http\Middleware\UnidadeSanitaria;

use Closure;
use Illuminate\Support\Facades\Auth;

class UnidadeSanitariaMiddleware
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
        $unidade_sanitaria_id = Auth::user()->userUnidadeSanitariaId();
        if (empty($unidade_sanitaria_id)) {
            return response()->json('Não possui permissões para aceder a esta Secção!', 403);
        }

        $request['unidade_sanitaria_id'] = $unidade_sanitaria_id;

        return $next($request);
    }
}
