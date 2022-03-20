<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\ApplicationSettings;

class ApplicationSettingsController extends Controller
{
    /**
     * Display the application settings.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $appSettings = ApplicationSettings::take(1)->first();

        return response()->json(['settings' => $appSettings]);
    }

    /**
     * Update the application settings.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'non_guest_email_domain' => 'required|string|min:5',
            'max_friends' => 'required|numeric|min:0',
            'min_preferences' => 'required|numeric|min:0',
            'max_preferences' => 'required|numeric|gte:min_preferences',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => __('errors.invalidRequestData'), 'errors' => $validator->messages()], 406);
        }

        $appSettings = ApplicationSettings::take(1)->first();

        if ($appSettings) {
            $appSettings->non_guest_email_domain = $request->input('non_guest_email_domain');
            $appSettings->max_friends = $request->input('max_friends');
            $appSettings->min_preferences = $request->input('min_preferences');
            $appSettings->max_preferences = $request->input('max_preferences');

            if ($appSettings->save()) {
                return response()->json(['message' => __('success.updatedApplicationSettings')]);
            } else {
                return response()->json(['message' => __('errors.unknownError')], 500);
            }
        } else {
            return response()->json(['message' => __('errors.notFound')], 404);
        }
    }
}
