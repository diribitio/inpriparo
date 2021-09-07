<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Timeframe;

class TimeframesController extends Controller
{
    /**
     * Display the timeframes of the project with the specified id
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($project_id)
    {
        $timeframes = Timeframe::where('project_id', $project_id)->get();

        if ($project) {
            $project->leader = $project->leader()->first();

            return response()->json(['project' => $project], 200);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404); 
        }
    }

    /**
     * Store a new timeframe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'until' => 'required|date|after:from',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $user = $this->authUser();

        $project = $user->project()->first();

        if (!$project) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => [__('validation.projectNotFound')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }

        $timeframe = new Timeframe;
        $timeframe->project_id = $project->id;
        $timeframe->from = $request->input('from');
        $timeframe->until = $request->input('until');

        if ($timeframe->save()) {
            return response()->json('', 200); 
        } else {
            return response()->json(['message' => __('errors.unknownError')], 500); 
        }
    }

    /**
     * Update the timeframe.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request) {
        $user = $this->authUser();

        $project = $user->project()->first();

        if (!$project) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => [__('validation.projectNotFound')],
            ]);
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }

        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'until' => 'required|date|after:from',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $timeframe = $project->timeframes()->get()->find($id);

        if ($timeframe) {
            $timeframe->from = $request->input('from');
            $timeframe->until = $request->input('until');

            try {
                if ($timeframe->save()) {
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
     * Delete the timeframe with the specified id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->authUser();

        $project = $user->project()->first();

        if (!$project) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => [__('validation.projectNotFound')],
            ]);
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }

        $timeframe = $project->timeframes()->get()->find($id);

        if ($timeframe) {
            if ($timeframe->delete()) {
                return response()->json('', 200); 
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500); 
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 500); 
        }
    }
}
