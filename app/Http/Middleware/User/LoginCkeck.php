<?php

namespace App\Http\Middleware\User;

use Closure;

class LoginCkeck
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
        if(session()->has('login') && session('login') === true) {
            return $next($request);
        } else {
            // 未登录返回 未登录
            // 正常情况不会出现未登录
            return  response(msg(6, __LINE__), 200);
        }
    }
}
