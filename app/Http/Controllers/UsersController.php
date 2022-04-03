<?php

namespace App\Http\Controllers;

use App\Models\GradeLevel;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use function MongoDB\BSON\toJSON;

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
     * Display all users that still need to be sorted (aka. all attendants).
     *
     * @return JsonResponse
     */
    public function index_unsorted(): JsonResponse
    {
        $attendants = User::with('roles')->whereHas("roles", function($q) {
            $q->where("name", "attendant");
        })->get();

        return response()->json(['users' => $attendants]);
    }

    /**
     *
     * @return JsonResponse
     */
    public function show_grade_level(): JsonResponse
    {
        $auth = $this->authUser();
        $user = User::find($auth->id);

        if ($user->hasRole('attendant') or $user->hasRole('participant') or $user->hasRole('leader') or $user->hasRole('assistant')) {
            $grade_level = $user->grade_level()->first();

            return response()->json(['grade_level' => $grade_level]);
        } else {
            return response()->json(['grade_level' => 'not needed']);
        }
    }

    /**
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
            return response()->json(['message' => __('errors.userCanNotBeConverted')], 422);
        }
    }

    /**
     *
     * @return JsonResponse
     */
    public function convert_self_to_guestAttendant(): JsonResponse
    {
        $auth = $this->authUser();
        $user = User::find($auth->id);

        if ($user->hasRole('attendant')) {
            $user->preferences()->delete();
            $user->offered_friendships()->delete();
            $user->received_friendships()->delete();
            $user->syncRoles(['user', 'guestAttendant']);
            return response()->json(['message' => __('success.convertedSelfToGuestAttendant')]);
        } else {
            return response()->json(['message' => __('errors.userCanNotBeConverted')], 422);
        }
    }

    /**
     *
     * @param int $id
     * @param int $projectId
     * @return JsonResponse
     */
    public function make_participant(int $id, int $projectId): JsonResponse
    {
        $user = User::find($id);

        $project = Project::find($projectId);

        if (($user->hasRole('attendant') or $user->hasRole('participant')) and $project) {
            $user->syncRoles(['user', 'participant']);
            $user->project()->associate($project);
            $user->project_role = 1;

            if ($user->save()) {
                return response()->json(['message' => __('success.convertedToParticipant')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else if ($user->hasRole('participant') and !$project) {
            $user->syncRoles(['user', 'attendant']);
            $user->project_id = 0;
            $user->project_role = 0;

            if ($user->save()) {
                return response()->json(['message' => __('success.convertedToAttendant')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.userCanNotBeConverted')], 422);
        }
    }

    /**
     *
     * @param int $grade_level
     * @return JsonResponse
     */
    public function store_grade_level(int $input_grade_level): JsonResponse
    {
        $user = $this->authUser();

        $grade_level = new GradeLevel();
        $grade_level->user_id = $user->id;
        $grade_level->grade_level = $input_grade_level;

        try {
            if ($grade_level->save()) {
                return response()->json(['message' => __('success.setGradeLevel')], 200);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } catch (QueryException $e) {
            if ($e->getCode() == '23000') {
                return response()->json(['message' => __('errors.alreadyExists')], 422);
            } else {
                return response()->json(['message' => $e], 500);
            }
        }
    }
}
