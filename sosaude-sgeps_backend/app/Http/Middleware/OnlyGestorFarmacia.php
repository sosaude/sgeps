<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OnlyGestorFarmacia
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
        $user = Auth::user();
        $user->load('role');
        if($user->role->id != 2){
            return response()->json(['error' => 'Usuário não possui permissões!']);
        }

        return $next($request);
    }
}
