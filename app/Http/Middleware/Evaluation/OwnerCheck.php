<?php

namespace App\Http\Middleware\Evaluation;

use App\Models\Evaluation;
use Closure;

class OwnerCheck
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
        $evaluation = Evaluation::query()->find($request->route('id'));
        if( (session("login") == true && $evaluation->publisher == session("uid")) // 发布者本人
            || session("ManagerLogin") == true // 或者管理员
        ) {
            return $next($request);
        } else {
            return response(msg(3, "权限不足或未登录" . __LINE__));
        }
    }
}
