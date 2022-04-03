<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Event;
use App\Models\Permission;

class Schedule
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

        return $next($request);

        if (in_array($requiredPermission, config('schedule.basic_permissions', []))) {
            return $next($request);
        }

        $event = Event::where('from', '<=', date("Y-m-d"))->where('until', '>=', date("Y-m-d"))->first();

        if ($this->permissionExists($requiredPermission) && $event) {
            if ($event->hasPermissionTo($requiredPermission) || in_array($requiredPermission, config('schedule.basic_permissions', []))) {
                return $next($request);
            }
        }


        if ($request->expectsJson()) {
            return response()->json(['message' => 'missingPermissions'], 403);
        }

        return abort(403);
    }

    private function permissionExists($permissionName) {
        return count(Permission::where('name', $permissionName)->get()) > 0;
    }
}
