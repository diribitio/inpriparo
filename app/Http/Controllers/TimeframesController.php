<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Timeframe;
use Illuminate\Validation\ValidationException;
use phpDocumentor\Reflection\Project;

class TimeframesController extends Controller
{
    /**
     * Display the timeframes of the project with the specified id
     *
     * @param int $project_id
     * @return JsonResponse
     */
    public function show(int $project_id): JsonResponse
    {
        $timeframes = Timeframe::where('project_id', $project_id)->get();

        $project = Project::find($project_id);

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
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
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
            $error = ValidationException::withMessages([
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
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->authUser();

        $project = $user->project()->first();

        if (!$project) {
            $error = ValidationException::withMessages([
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
     * Delete the timeframe with the specified id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = $this->authUser();

        $project = $user->project()->first();

        if (!$project) {
            $error = ValidationException::withMessages([
                'project_id' => [__('validation.projectNotFound')],
            ]);
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }

        $timeframe = $project->timeframes()->get()->find($id);

        if ($timeframe) {
            if ($timeframe->delete()) {
                return response()->json('');
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 500);
        }
    }
}
