<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSettings;
use App\Models\Preference;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PreferencesController extends Controller
{
    /**
     * Display all preferences.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $preferences = Preference::all();

        $preferences->each(function ($preferences) {
            $preferences->user = User::find($preferences->user_id);
            $preferences->project = Project::find($preferences->project_id);
        });

        return response()->json(['preferences' => $preferences]);
    }

    /**
     * Display the preferences with the specified id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $preferences = Preference::all();

        if ($preferences) {
            $preferences->user = User::find($preferences->user_id);
            $preferences->project = Project::find($preferences->project_id);

            return response()->json(['preferences' => $preferences]);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Display the preferences associated with the user.
     *
     * @return JsonResponse
     */
    public function show_associated(): JsonResponse
    {
        $user = $this->authUser();

        $preferences = $user->preferences;
        $preferences->each(function ($preferences) {
            $preferences->user = User::find($preferences->user_id);
            $preferences->project = Project::find($preferences->project_id);
        });

        return response()->json(['preferences' => $preferences]);
    }

    /**
     * Store a new preferences.
     *
     * @param int $project_id
     * @return JsonResponse
     */
    public function store(int $project_id): JsonResponse
    {

        $user = $this->authUser();

        $project = Project::find($project_id);

        $appSettings = ApplicationSettings::take(1)->first();

        if (count($user->preferences) >= $appSettings->max_preferences) {
            return response()->json(['message' => __('validation.reachedPreferenceLimit')], 406);
        }

        if (!$project) {
            $error = ValidationException::withMessages([
                'project_id' => [__('validation.projectNotFound')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (!$project->authorized) {
            $error = ValidationException::withMessages([
                'project_id' => [__('validation.projectNotAuthorized')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (Preference::where('user_id', $user->id)->where('project_id', $project->id)->get()->isNotEmpty()) {
            $error = ValidationException::withMessages([
                'project_id' => [__('validation.alreadyExists')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }  else if ($user->project_id == $project->id) {
            $error = ValidationException::withMessages([
                'project_id' => [__('validation.musntBeYourProject')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }


        $preference = new Preference;

        $preference->user_id = $user->id;
        $preference->project_id = $project->id;

        try {
            if ($preference->save()) {
                return response()->json(['message' => __('success.storedPreference')]);
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
     * Delete the preference with the specified id.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = $this->authUser();

        $preference = $user->preferences->find($id);

        if ($preference) {
            if ($preference->delete()) {
                return response()->json(['message' => __('success.destroyedPreference')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }
}
