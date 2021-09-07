<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Preference;
use App\Models\User;
use App\Models\Project;

class PreferencesController extends Controller
{
    /**
     * Display all preferences.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $preferences = Preference::all();

        $preferences->each(function ($preferences) {
            $preferences->user = User::find($preferences->user_id);
            $preferences->project = Project::find($preferences->project_id);
        });

        return response()->json(['preferences' => $preferences], 200);
    }

    /**
     * Display the preferences with the specified id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $preferences = Preference::all();

        if ($preferences) {
            $preferences->user = User::find($preferences->user_id);
            $preferences->project = Project::find($preferences->project_id);

            return response()->json(['preferences' => $preferences], 200);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404); 
        }
    }

    /**
     * Display the preferences associated with the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_associated()
    {
        $user = $this->authUser();

        $preferences = $user->preferences;
        $preferences->each(function ($preferences) {
            $preferences->user = User::find($preferences->user_id);
            $preferences->project = Project::find($preferences->project_id);
        });

        return response()->json(['preferences' => $preferences], 200);
    }

    /**
     * Store a new preferences.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store($project_id) {

        $user = $this->authUser();

        $project = find($project_id);

        if (!$project) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => [__('validation.projectNotFound')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (!$project->authorized) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => [__('validation.projectNotAuthorized')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (Preference::where('user_id', $user->id)->where('project_id', $project->id)->get()->isNotEmpty()) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => [__('validation.alreadyExists')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }  else if ($user->project_id == $project->id) {
            $error = \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => [__('validation.musntBeYourProject')],
             ]);
             return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }


        $preference = new Preference;

        $preference->user_id = $user->id;
        $preference->project_id = $project->id;

        try {
            if ($preference->save()) {
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
     * Delete the preference with the specified id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->authUser();

        $preference = $user->preferences->find($id);

        if ($preference) {
            if ($preference->delete()) {
                return response()->json('', 200); 
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500); 
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }
}
