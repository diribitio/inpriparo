<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Event;

class EventsController extends Controller
{
    /**
     * Display all events.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $events = Event::with('permissions')->orderBy('from')->get();

        return response()->json(['events' => $events]);
    }

    /**
     * Store a new event.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
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
            return response()->json(['message' => __('success.storedEvent'), 'event' => $event]);
        } else {
            return response()->json(['message' => __('errors.unknownError')], 500);
        }
    }

    /**
     * Update an event.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
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
                return response()->json(['message' => __('success.updatedEvent')]);
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
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function sync_permissions(int $id, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $event = Event::find($id);

        if ($event) {
            if ($event->syncPermissions(array_merge($request->input('permissions'), config('schedule.basic_permissions', [])))) {
                return response()->json('');
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
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $event = Event::find($id);

        if ($event) {
            if ($event->delete()) {
                return response()->json(['message' => __('success.destroyedEvent')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 500);
        }
    }
}
