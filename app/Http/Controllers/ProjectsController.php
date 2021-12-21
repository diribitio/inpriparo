<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

class ProjectsController extends Controller
{
    /**
     * Display all projects.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = Project::all();

        $projects->each(function ($project) {
            $project->leader = $project->leader()->first();
        });

        return response()->json(['projects' => $projects], 200);
    }

    /**
     * Display the project with the specified id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::find($id);

        if ($project) {
            $project->leader = $project->leader()->first();

            return response()->json(['project' => $project], 200);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Display the project associated with the user. (Regaredless of the role)
     *
     * @return \Illuminate\Http\Response
     */
    public function show_associated()
    {
        $user = $this->authUser();

        $project = $user->project()->first();

        if ($project) {
            $project->leader = $project->leader()->first();

            return response()->json(['project' => $project], 200);
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }

    /**
     * Store a new project and assign the user to it.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        if ($request->hasFile('image')) {
            $validator = Validator::make($request->all(), [
                'topic' => 'required|string',
                'image' => 'image',
                'title' => 'required|string|min:5',
                'description' => 'required|string|min:20',
                'cost' => 'required|numeric|min:0',
                'min_grade' => 'required|numeric',
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
                'min_grade' => 'required|numeric',
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
            $path = $request->file('image')->storeAs('public/images', $fileNameToStore);
            $project->image = $fileNameToStore;
        }

        try {
            if ($project->save()) {
                $user->syncRoles(['user', 'leader']);
                $user->project()->associate($project);
                $user->project_role = 3;

                if ($user->save()) {
                    return response()->json('', 200);
                } else {
                    return response()->json(['message' => __('errors.updateAccount')], 500);
                }
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
     * Update a users project.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_associated(Request $request) {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
            'title' => 'required|string|min:5',
            'description' => 'required|string|min:20',
            'cost' => 'required|numeric|min:0',
            'min_grade' => 'required|numeric',
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
     * Toggle the authorized property of a project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleAuthorized($id) {
        $project = Project::find($id);

        if ($project) {
            $project->authorized = !$project->authorized;

            if ($project->save()) {
                return response()->json('', 200);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::find($id);

        if ($project) {
            if ($project->leader()->exists()) {
                $leader = $project->leader->first();
                $leader->syncRoles(['user', 'attendant']);
                $leader->project_id = 0;
                $leader->project_role = 0;
                if ($leader->save()) {
                    // TODO notify user that their project was deleted
                } else {
                    // $message +=  json(['message' => __('errors.updateAccount')]);
                }
            }

            if ($project->assistants()->exists()) {
                $assistants = $project->assistants;

                $assistants->each(function ($assistant, $key) use ($project) {
                    $assistant->syncRoles(['user', 'attendant']);
                    $assistant->project_id = 0;
                    $assistant->project_role = 0;
                    if ($assistant->save()) {
                        // TODO notify user that their project was deleted
                    } else {
                        // $message +=  json(['message' => __('errors.updateAccount')]);
                    }
                });

                unset($project->assistants);
            }

            $project->timeframes()->delete();

            // TODO check for messages and delete them

            if ($project->image != null && $project->image != '') {
                Storage::delete('public/images/'. $project->image);
            }

            if ($project->delete()) {
                return response()->json('', 200);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }
}
