<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionsController extends Controller
{
    /**
     * Display all permissions.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $permission = Permission::orderBy('name')->get();

        return response()->json(['permissions' => $permission]);
    }
}
