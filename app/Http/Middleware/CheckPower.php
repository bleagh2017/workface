<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class CheckPower
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
        if($request->user()->user_type != 1 && $request->user()->user_type != 9){
            return response(json_encode(["message"=>"您不是workface管理员"]),422);
        }
        return $next($request);
    }
}
