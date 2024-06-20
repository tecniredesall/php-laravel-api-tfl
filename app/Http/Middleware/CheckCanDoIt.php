<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class CheckCanDoIt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  String  $permission
     * @return mixed
     */
    public function handle( $request, Closure $next, $permission ){
        if ($permission !== 27) {
            if (\App\Api::iCan(auth()->id(), $permission))
                return $next($request);
            else
                return response()->json(['status' => false, 'message' => "You don't have access in this module"], 403);
        } else {
            $obj = \App\Users::with(array('security'))->find(auth()->id())->security->toArray();
            foreach ($obj as $key => $val) {
                $array[] = $val['id'];
            }
            if (in_array(27, $array))
                return $next($request);
            else
                return response()->json(['status' => false, 'message' => "You don't have access in this module"], 403);
        }

    }
}