<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Friendship;
use App\Models\User;

class FriendshipsController extends Controller
{
    /**
     * Display all friendships.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $friendships = Friendship::all();

        $friendships->each(function ($friendship) {
            $friendship->applicant = User::find($friendship->applicant_id);
            $friendship->respondent = User::find($friendship->respondent_id);
        });

        return response()->json(['friendships' => $friendships], 200);
    }

    /**
     * Display the friendship with the specified id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $friendship = Friendship::find($id);

        if ($friendship) {
            $friendship->applicant = User::find($friendship->applicant_id);
            $friendship->respondent = User::find($friendship->respondent_id);

            return response()->json(['friendship' => $friendship], 200);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404); 
        }
    }

    /**
     * Display the friendships associated with the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_associated()
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

        return response()->json(['offered_friendships' => $offered_friendships, 'received_friendships' => $received_friendships], 200);
    }

    /**
     * Store a new friendship.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'respondent_email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $user = $this->authUser();

        $respondent = User::where('email', $request->input('respondent_email'))->first();

        if (!$respondent) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'respondent_email' => [__('validation.user')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (!$respondent->can('friendships.accept')) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'respondent_email' => [__('validation.userCannotAcceptFriendship')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if ($respondent->id == $user->id) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'respondent_email' => [__('validation.musntBeYou')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (Friendship::where('applicant_id', $user->id)->where('respondent_id', $respondent->id)->get()->isNotEmpty() || Friendship::where('applicant_id', $respondent->id)->where('respondent_id', $user->id)->get()->isNotEmpty()) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'respondent_email' => [__('validation.alreadyExists')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }

        $friendship = new Friendship;

        $friendship->applicant_id = $user->id;
        $friendship->respondent_id = $respondent->id;

        try {
            if ($friendship->save()) {
                return response()->json('', 200);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return response()->json(['message' => __('errors.alreadyExists')], 422);
            } else {
                return response()->json(['message' => $e], 500);
            }
        }
    }

    /**
     * Accept a users friendship.
     *
     * @param  int  $applicant_id
     * @return \Illuminate\Http\Response
     */
    public function accept($id) {

        $user = $this->authUser();

        $friendship = $user->received_friendships->find($id);

        if ($friendship) {
            $friendship->state = 1;

            try {
                if ($friendship->save()) {
                    return response()->json('', 200); 
                } else {
                    return response()->json(['message' => __('errors.unknownError')], 500);
                }
            } catch (\Illuminate\Database\QueryException $e) {
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function authorise($id) {

        $friendship = Friendship::find($id);

        if ($friendship) {

            if ($friendship->state < 1) {
                return response()->json(['message' => __('errors.friendshipNotAccepted')], 406);
            }

            $friendship->state = 2;

            try {
                if ($friendship->save()) {
                    return response()->json('', 200); 
                } else {
                    return response()->json(['message' => __('errors.unknownError')], 500);
                }
            } catch (\Illuminate\Database\QueryException $e) {
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function decline($id) {

        $friendship = Friendship::find($id);

        if ($friendship) {

            if ($friendship->state < 2) {
                return response()->json(['message' => __('errors.friendshipNotAuthorized')], 406);
            }

            $friendship->state = 1;

            try {
                if ($friendship->save()) {
                    return response()->json('', 200); 
                } else {
                    return response()->json(['message' => __('errors.unknownError')], 500);
                }
            } catch (\Illuminate\Database\QueryException $e) {
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->authUser();

        $friendship = $user->offered_friendships->concat($user->received_friendships)->find($id);

        if ($friendship) {
            if ($friendship->delete()) {
                return response()->json('', 200); 
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500); 
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }
}
