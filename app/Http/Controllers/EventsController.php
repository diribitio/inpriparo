<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Permission;

class EventsController extends Controller
{
    /**
     * Display all events.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::with('permissions')->orderBy('from')->get();

        return response()->json(['events' => $events], 200);
    }

    /**
     * Store a new event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5',
            'description' => 'required|string|min:20',
            'from' => 'required|date_format:"Y-m-d"',
            'until' => 'required|date_format:"Y-m-d"|after:from',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $event = new Event;
        $event->title = $request->input('title');
        $event->description = $request->input('description');
        $event->from = $request->input('from');
        $event->until = $request->input('until');

        if ($event->save()) {
            return response()->json(['event' => $event], 200); 
        } else {
            return response()->json(['message' => __('errors.unknownError')], 500); 
        }
    }

    /**
     * Update an event.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5',
            'description' => 'required|string|min:20',
            'from' => 'required|date_format:"Y-m-d"',
            'until' => 'required|date_format:"Y-m-d"|after:from',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $event = Event::find($id);

        if ($event) {
            $event->title = $request->input('title');
            $event->description = $request->input('description');
            $event->from = $request->input('from');
            $event->until = $request->input('until');

            if ($event->save()) {
                return response()->json('', 200); 
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404); 
        }
    }

    /**
     * Sync an event's permission
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function syncPermissions($id, Request $request) {
        $validator = Validator::make($request->all(), [
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $event = Event::find($id);

        if ($event) {
            if ($event->syncPermissions($request->input('permissions'))) {
                return response()->json('', 200); 
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404); 
        }
    }

    /**
     * Delete the event with the specified id.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $event = Event::find($id);

        if ($event) {
            if ($event->delete()) {
                return response()->json('', 200); 
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500); 
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 500); 
        }
    }
}
