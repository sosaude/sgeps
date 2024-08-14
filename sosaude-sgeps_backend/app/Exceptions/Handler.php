<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        /* if($exception instanceof \Illuminate\Database\QueryException) {
            if($request->expectsJson()) {
                return response()->json(['message' => $exception->getMessage()], 500);
            }
        } */

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Não autenticado/autorizado'], $exception->getStatusCode());
            }
        }

        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            if($request->expectsJson()) {
                return response()->json(['message' => 'Não autenticado/autorizado'], 401);  
            }
            
        } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            if($request->expectsJson()) {
                return response()->json(['message' => 'Não autenticado/autorizado'], 401);  
            }
            
        } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
            if($request->expectsJson()) {
                return response()->json(['message' => 'Não autenticado/autorizado'], 401);  
            }
            
        }
        
        return parent::render($request, $exception);
    }
}
