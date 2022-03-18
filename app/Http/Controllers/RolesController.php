<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RolesController extends Controller
{
    /**
     * Display all roles.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return response()->json(['roles' => $roles]);
    }

    /**
     * Store a new role.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $role = Role::create(['name' => $request->input('name')]);

        if ($role) {
            return response()->json(['created_role' => Role::with('permissions')->where('id', $role->id)->first()]);
        } else {
            return response()->json(['message' => __('errors.unknownError')], 500);
        }
    }

    /**
     * Toggle a role's permission
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function togglePermission(int $id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $role = Role::find($id);
        $permission = Permission::where('name', $request->input('name'))->first();

        if ($role && $permission) {

            if (!$role->hasPermissionTo($permission)) {
                if ($role->givePermissionTo($permission)) {
                    return response()->json(['message' => __('success.toggledPermission')]);
                } else {
                    return response()->json(['message' => __('errors.unknownError')], 500);
                }
            } else {
                if ($role->revokePermissionTo($permission)) {
                    return response()->json('');
                } else {
                    return response()->json(['message' => __('errors.unknownError')], 500);
                }
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Delete the role with the specified id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $role = Role::find($id);

        if ($role) {
            $users = User::role($role->name)->get();

            if (count($users) > 0) {
                $error = ValidationException::withMessages([
                    'id' => [__('validation.roleInUse')],
                 ]);
                 return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
            }

            if ($role->delete()) {
                return response()->json(['message' => __('success.destroyedRole')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 500);
        }
    }
}
