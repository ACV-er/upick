<?php

namespace App\Http\Middleware\Evaluation;

use App\Models\Evaluation;
use Closure;

class ExistCheck
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
        if(!$evaluation) {
            return msg(3, "目标不存在" . __LINE__);
        } else {
            return $next($request);
        }
    }
}
