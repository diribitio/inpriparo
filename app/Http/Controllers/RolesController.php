<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class RolesController extends Controller
{
    /**
     * Display all roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
    
        return response()->json(['roles' => $roles], 200);
    }

    /**
     * Store a new role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:5|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $role = Role::create(['name' => $request->input('name')]);

        if ($role) {
            return response()->json(['created_role' => Role::with('permissions')->where('id', $role->id)->first()], 200); 
        } else {
            return response()->json(['message' => __('errors.unknownError')], 500);
        }
    }

    /**
     * Toggle a role's permission
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function togglePermission($id, Request $request) {
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
                    return response()->json('', 200); 
                } else {
                    return response()->json(['message' => __('errors.unknownError')], 500);
                }
            } else {
                if ($role->revokePermissionTo($permission)) {
                    return response()->json('', 200); 
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if ($role) {
            $users = User::role($role->name)->get();

            if (count($users) > 0) {
                $error = \Illuminate\Validation\ValidationException::withMessages([
                    'id' => [__('validation.roleInUse')],
                 ]);
                 return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
            }

            if ($role->delete()) {
                return response()->json('', 200); 
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500); 
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 500); 
        }
    }
}
