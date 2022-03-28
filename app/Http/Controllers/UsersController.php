<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    /**
     * Display all users.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $users = User::with('roles')->get();

        return response()->json(['users' => $users]);
    }

    /**
     * Display all users.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function convert_to_guestAttendant(int $id): JsonResponse
    {
        $user = User::find($id);

        if ($user->hasRole('attendant')) {
            $user->preferences()->delete();
            $user->offered_friendships()->delete();
            $user->received_friendships()->delete();
            $user->syncRoles(['user', 'guestAttendant']);
            return response()->json(['message' => __('success.convertedToGuestAttendant')]);
        } else {
            return response()->json(['message' => __('error.userCanNotBeConverted')]);
        }
    }
}
