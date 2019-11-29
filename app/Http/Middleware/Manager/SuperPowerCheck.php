<?php

namespace App\Http\Middleware\Manager;

use Closure;

class SuperPowerCheck
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
        if(session()->has('ManagerLogin') && session('level') === 0) {
            return $next($request);
        } else {
            return  response(msg(3, "权限不足" .__LINE__), 200);
        }
    }
}
