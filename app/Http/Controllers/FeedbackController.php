<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\User;

class FeedbackController extends Controller
{
    /**
     * Display all feedback.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $feedback = Feedback::all();

        return response()->json(['feedback' => $feedback], 200);
    }

    /**
     * Display the feedback with the specified id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $feedback = Feedback::find($id);

        if ($feedback) {
            $feedback->creator = User::find($feedback->created_by);

            return response()->json(['feedback' => $feedback], 200);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404); 
        }
    }

    /**
     * Store new feedback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
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
            return response()->json('', 200); 
        } else {
            return response()->json(['message' => __('errors.unknownError')], 500); 
        }
    }

    /**
     * Delete the feedback with the specified id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $feedback = Feedback::find($id);

        if ($feedback) {
            if ($feedback->delete()) {
                return response()->json('', 200); 
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500); 
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 500); 
        }
    }
}
