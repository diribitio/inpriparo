<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FriendshipsController extends Controller
{
    /**
     * Display all friendships.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $friendships = Friendship::all();

        $friendships->each(function ($friendship) {
            $friendship->applicant = User::find($friendship->applicant_id);
            $friendship->respondent = User::find($friendship->respondent_id);
        });

        return response()->json(['friendships' => $friendships]);
    }

    /**
     * Display the friendship with the specified id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $friendship = Friendship::find($id);

        if ($friendship) {
            $friendship->applicant = User::find($friendship->applicant_id);
            $friendship->respondent = User::find($friendship->respondent_id);

            return response()->json(['friendship' => $friendship]);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Display the friendships associated with the user.
     *
     * @return JsonResponse
     */
    public function show_associated(): JsonResponse
    {
        $user = $this->authUser();

        $offered_friendships = $user->offered_friendships;
        $offered_friendships->each(function ($offered_friendship) {
            $offered_friendship->applicant = User::find($offered_friendship->applicant_id);
            $offered_friendship->respondent = User::find($offered_friendship->respondent_id);
        });
        $received_friendships = $user->received_friendships;
        $received_friendships->each(function ($received_friendship) {
            $received_friendship->applicant = User::find($received_friendship->applicant_id);
            $received_friendship->respondent = User::find($received_friendship->respondent_id);
        });

        return response()->json(['offered_friendships' => $offered_friendships, 'received_friendships' => $received_friendships]);
    }

    /**
     * Store a new friendship.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'respondent_email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $user = $this->authUser();

        $respondent = User::where('email', $request->input('respondent_email'))->first();

        if (!$respondent) {
            $error = ValidationException::withMessages([
                'respondent_email' => [__('validation.userNotFound')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (!$respondent->can('friendships.accept')) {
            $error = ValidationException::withMessages([
                'respondent_email' => [__('validation.userCannotAcceptFriendship')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if ($respondent->id == $user->id) {
            $error = ValidationException::withMessages([
                'respondent_email' => [__('validation.musntBeYou')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (Friendship::where('applicant_id', $user->id)->where('respondent_id', $respondent->id)->get()->isNotEmpty() || Friendship::where('applicant_id', $respondent->id)->where('respondent_id', $user->id)->get()->isNotEmpty()) {
            $error = ValidationException::withMessages([
                'respondent_email' => [__('validation.alreadyExists')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }

        $friendship = new Friendship;

        $friendship->applicant_id = $user->id;
        $friendship->respondent_id = $respondent->id;

        try {
            if ($friendship->save()) {
                return response()->json(['message' => __('success.storedFriendship')]);
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

    /**
     * Accept a user's friendship.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function accept(int $id): JsonResponse
    {

        $user = $this->authUser();

        $friendship = $user->received_friendships->find($id);

        if ($friendship) {
            $friendship->state = 1;

            try {
                if ($friendship->save()) {
                    return response()->json(['message' => __('success.acceptedFriendship')]);
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
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Authorize a friendship.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function authorise(int $id): JsonResponse
    {

        $friendship = Friendship::find($id);

        if ($friendship) {

            if ($friendship->state < 1) {
                return response()->json(['message' => __('errors.friendshipNotAccepted')], 406);
            }

            $friendship->state = 2;

            try {
                if ($friendship->save()) {
                    return response()->json(['message' => __('success.authorizedFriendship')]);
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
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Decline a friendship.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function decline(int $id): JsonResponse
    {

        $friendship = Friendship::find($id);

        if ($friendship) {

            if ($friendship->state < 2) {
                return response()->json(['message' => __('errors.friendshipNotAuthorized')], 406);
            }

            $friendship->state = 1;

            try {
                if ($friendship->save()) {
                    return response()->json(['message' => __('success.declinedFriendship')]);
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
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Delete the friendship with the specified id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = $this->authUser();

        $friendship = $user->offered_friendships->concat($user->received_friendships)->find($id);

        if ($friendship) {
            if ($friendship->delete()) {
                return response()->json(['message' => __('success.deletedFriendship')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }
}
