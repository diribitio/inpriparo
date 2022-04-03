<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ProjectDeletedNotification;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Validation\ValidationException;

class ProjectsController extends Controller
{
    /**
     * Display all projects.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $projects = Project::all();

        $projects->each(function ($project) {
            $project->leader = $project->leader()->first();
        });

        return response()->json(['projects' => $projects], 200);
    }

    /**
     * Display all projects in greater detail.
     *
     * @return JsonResponse
     */
    public function index_detailed(): JsonResponse
    {
        $projects = Project::all();

        $projects->each(function ($project) {
            $project->leader = $project->leader()->first();
            $project->assistants = $project->assistants()->with('preferences')->get();
            $project->participants = $project->participants()->with('preferences', 'grade_level')->get();
        });

        return response()->json(['projects' => $projects], 200);
    }

    /**
     * Display the project with the specified id.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $project = Project::find($id);

        if ($project) {
            $project->leader = $project->leader()->first();
            $project->assistants = $project->assistants()->get();
            $project->participants = $project->participants()->get();

            return response()->json(['project' => $project], 200);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Display the project associated with the user. (Regaredless of the role)
     *
     * @return JsonResponse
     */
    public function show_associated(): JsonResponse
    {
        $user = $this->authUser();

        $project = $user->project()->first();

        if ($project) {
            $project->leader = $project->leader()->first();
            $project->assistants = $project->assistants()->get();
            $project->participants = $project->participants()->get();

            return response()->json(['project' => $project], 200);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Store a new project and assign the user to it.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->hasFile('image')) {
            $validator = Validator::make($request->all(), [
                'topic' => 'required|string',
                'image' => 'image',
                'title' => 'required|string|min:5',
                'description' => 'required|string|min:20',
                'cost' => 'required|numeric|min:0',
                'min_grade' => 'required|numeric|min:0',
                'max_grade' => 'required|numeric|gte:min_grade',
                'min_participants' => 'required|numeric|min:0',
                'max_participants' => 'required|numeric|gte:min_participants',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'topic' => 'required|string',
                'title' => 'required|string|min:5',
                'description' => 'required|string|min:20',
                'cost' => 'required|numeric|min:0',
                'min_grade' => 'required|numeric|min:0',
                'max_grade' => 'required|numeric|gte:min_grade',
                'min_participants' => 'required|numeric|min:0',
                'max_participants' => 'required|numeric|gte:min_participants',
            ]);
        }

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $user = $this->authUser();

        $project = new Project;

        $project->topic = $request->input('topic');
        $project->title = $request->input('title');
        $project->leader_id = $user->id;
        $project->description = $request->input('description');
        $project->cost = $request->input('cost');
        $project->min_grade = $request->input('min_grade');
        $project->max_grade = $request->input('max_grade');
        $project->min_participants = $request->input('min_participants');
        $project->max_participants = $request->input('max_participants');

        if ($user->can('projects.authorize_associated')) {
            $project->authorized = 1;
        }

        if ($request->hasFile('image')) {
            $filenameWithExt = $request->file('image')->getClientOriginalName();
            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore= $filename.'_'.time().'.'.$extension;
            // don't remove the 'unused' variable because it is necessary for storing the image!
            $path = $request->file('image')->storeAs('public/images', $fileNameToStore);
            $project->image = $fileNameToStore;
        }

        try {
            if ($project->save()) {
                if ($user->hasRole('attendant')) {
                    $user->syncRoles(['user', 'leader']);
                } else {
                    $user->syncRoles(['user', 'guestLeader']);
                }
                $user->project()->associate($project);
                $user->project_role = 3;

                if ($user->save()) {
                    return response()->json(['message' => __('success.storedProject')]);
                } else {
                    return response()->json(['message' => __('errors.updateAccount')], 500);
                }
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
     * Update a users project.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update_associated(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
            'title' => 'required|string|min:5',
            'description' => 'required|string|min:20',
            'cost' => 'required|numeric|min:0',
            'min_grade' => 'required|numeric|min:0',
            'max_grade' => 'required|numeric|gte:min_grade',
            'min_participants' => 'required|numeric|min:0',
            'max_participants' => 'required|numeric|gte:min_participants',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $user = $this->authUser();

        $project = $user->project()->first();

        if ($project) {
            $project->topic = $request->input('topic');
            $project->title = $request->input('title');
            $project->description = $request->input('description');
            $project->cost = $request->input('cost');
            $project->min_grade = $request->input('min_grade');
            $project->max_grade = $request->input('max_grade');
            $project->min_participants = $request->input('min_participants');
            $project->max_participants = $request->input('max_participants');

            try {
                if ($project->save()) {
                    return response()->json(['message' => __('success.updatedProject')]);
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
     * Promote the specified user with the attendant_email to assistant rank.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function promote_assistant(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attendant_email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $user = $this->authUser();
        $project = $user->project()->first();

        $potentialAssistant = User::where('email', $request->input('attendant_email'))->first();

        if (!$potentialAssistant) {
            $error = ValidationException::withMessages([
                'attendant_email' => [__('validation.userNotFound')],
            ]);
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (!$potentialAssistant->hasRole('attendant')) {
            $error = ValidationException::withMessages([
                'attendant_email' => [__('validation.userCannotBePromoted')],
            ]);
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        } else if (!$potentialAssistant->can('projects.store')) {
            $error = ValidationException::withMessages([
                'attendant_email' => [__('validation.userCannotBePromoted')],
            ]);
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $error-> errors()], 406);
        }


        try {
            $potentialAssistant->syncRoles(['user', 'assistant']);
            $potentialAssistant->project()->associate($project);
            $potentialAssistant->project_role = 2;

            if ($potentialAssistant->save()) {
                return response()->json(['message' => __('success.updatedProject')]);
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
     * Demote the specified user with the attendant_email from assistant rank.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function demote_assistant(int $id): JsonResponse
    {
        $user = $this->authUser();
        $project = $user->project()->first();

        $assistant = $project->assistants()->where('id', $id)->first();

        if (!$assistant) {
            return response()->json(['message' => __('errors.notFound')], 406);
        } else if (!$assistant->hasRole('assistant')) {
            return response()->json(['message' => __('errors.invalidRequestData')], 406);
        }


        try {
            $assistant->syncRoles(['user', 'attendant']);
            $assistant->project_id = 0;
            $assistant->project_role = 0;

            if ($assistant->save()) {
                return response()->json(['message' => __('success.updatedProject')]);
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
     * Toggle the authorized property of a project.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggle_authorized(int $id): JsonResponse
    {
        $project = Project::find($id);

        if ($project) {
            $project->authorized = !$project->authorized;

            if ($project->save()) {
                return response()->json(['message' => __('success.toggledProjectAuthorized')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Delete the specified project.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $project = Project::find($id);

        if ($project) {
            if ($project->leader()->exists()) {
                $leader = $project->leader->first();
                if ($leader->hasRole('leader')) {
                    $leader->syncRoles(['user', 'attendant']);
                } else {
                    $leader->syncRoles(['user', 'guestAttendant']);
                }
                $leader->project_id = 0;
                $leader->project_role = 0;
                if ($leader->save()) {
                    $leader->sendProjectDeletedNotification();
                }
            }

            if ($project->assistants()->exists()) {
                $assistants = $project->assistants;

                $assistants->each(function ($assistant, $key) use ($project) {
                    $assistant->syncRoles(['user', 'attendant']);
                    $assistant->project_id = 0;
                    $assistant->project_role = 0;
                    if ($assistant->save()) {
                        $assistant->sendProjectDeletedNotification();
                    }
                });

                unset($project->assistants);
            }

            $project->timeframes()->delete();

            if ($project->image != null && $project->image != '') {
                Storage::delete('public/images/'. $project->image);
            }

            if ($project->delete()) {
                return response()->json(['message' => __('success.destroyedProject')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }
}
