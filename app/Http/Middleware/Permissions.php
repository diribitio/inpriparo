<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class Permissions
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
        $actionName = explode('@', Route::getCurrentRoute()->getActionName());
        $action = $actionName[1];
        $object = str_replace('controller', '', strtolower(str_replace('App\Http\Controllers\\', '', $actionName[0])));
        $requiredPermission = $object . '.' . $action;

        if ($requiredPermission) {
            if (Auth::user()->can($requiredPermission) || 1 == 1) {
                return $next($request);
            }
        }
        
        if ($request->expectsJson()) {
            return response()->json(['message' => $requiredPermission], 403);
        }

        return abort(403);
    }
}
