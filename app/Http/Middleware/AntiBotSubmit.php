<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AntiBotSubmit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        echo $request->input('fax');

        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            if ($request->input('fax') != false) {
                return response()->json('I\'m a teapot', 418);
            }
        }

        return $next($request);
    }
}
