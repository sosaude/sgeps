<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        $roles_array = explode(":", $roles);
        if(!$request->user()->hasRole($roles_array)){
            return response()->json(['error' => 'Usuário não possui permissões!'], 403);
        }
        return $next($request);
    }
}
