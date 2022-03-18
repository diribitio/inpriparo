<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\User;

class FeedbackController extends Controller
{
    /**
     * Display all feedback.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $feedback = Feedback::all();

        return response()->json(['feedback' => $feedback]);
    }

    /**
     * Display the feedback with the specified id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $feedback = Feedback::find($id);

        if ($feedback) {
            $feedback->creator = User::find($feedback->created_by);

            return response()->json(['feedback' => $feedback]);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Store new feedback.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'short_description' => 'required|string|min:5',
            'full_description' => 'required|string|min:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $user = $this->authUser();

        $feedback = new Feedback;
        $feedback->type = $request->input('type');
        $feedback->short_description = $request->input('short_description');
        $feedback->full_description = $request->input('full_description');
        $feedback->created_by = $user->id;

        if ($feedback->save()) {
            return response()->json(['message' => __('success.storedFeedback')]);
        } else {
            return response()->json(['message' => __('errors.unknownError')], 500);
        }
    }

    /**
     * Delete the feedback with the specified id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $feedback = Feedback::find($id);

        if ($feedback) {
            if ($feedback->delete()) {
                return response()->json(['message' => __('success.destroyedFeedback')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 500);
        }
    }
}
