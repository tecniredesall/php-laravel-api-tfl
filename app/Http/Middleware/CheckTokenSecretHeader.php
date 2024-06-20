<?php

namespace App\Http\Middleware;

use Closure;

class CheckTokenSecretHeader
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
        $secret=!empty($request->header('secret'))?$request->header('secret'):$request->input('secret');

        if($secret!=env('TOKEN_SECRET')){
            return response()->json(['error'=>'Unauthorized','secret'=>$request->header('secret')],401);
        }
        return $next($request);
    }
}
