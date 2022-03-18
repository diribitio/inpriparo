<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\FriendshipsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\PreferencesController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TimeframesController;

use App\Models\Event;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
->middleware(['verified']) for routes where the email needs to be verified
*/

Route::middleware('auth')->group(function () {
    Route::middleware('permissions')->group(function () {
        Route::middleware('schedule')->group(function () {
            // General routes (mostly for admins)

            Route::get('users', [UsersController::class, 'index']);

            Route::get('feedback', [FeedbackController::class, 'index']);
            Route::get('feedback/{id}', [FeedbackController::class, 'show']);
            Route::post('feedback', [FeedbackController::class, 'store']);
            Route::delete('feedback/{id}', [FeedbackController::class, 'destroy']);

            Route::get('projects', [ProjectsController::class, 'index']);
            Route::get('projects/{id}', [ProjectsController::class, 'show']);
            Route::patch('projects/{id}', [ProjectsController::class, 'toggleAuthorized']);
            Route::delete('projects/{id}', [ProjectsController::class, 'destroy']);
            Route::get('projects/{project_id}/timeframes', [TimeframesController::class, 'show']);

            Route::get('friendships', [FriendshipsController::class, 'index']);
            Route::get('friendships/{id}', [FriendshipsController::class, 'show']);
            Route::put('friendships/authorise/{id}', [FriendshipsController::class, 'authorise']);
            Route::put('friendships/decline/{id}', [FriendshipsController::class, 'decline']);

            Route::get('preferences', [PreferencesController::class, 'index']);
            Route::get('preferences/{id}', [PreferencesController::class, 'show']);

            Route::get('permissions', [PermissionsController::class, 'index']);

            Route::get('roles', [RolesController::class, 'index']);
            Route::post('roles', [RolesController::class, 'store']);
            Route::patch('roles/{id}', [RolesController::class, 'togglePermission']);
            Route::delete('roles/{id}', [RolesController::class, 'destroy']);

            // Routes which are user specific

            Route::get('user/project', [ProjectsController::class, 'show_associated']);
            Route::post('user/project', [ProjectsController::class, 'store']);
            Route::put('user/project', [ProjectsController::class, 'update_associated']);

            Route::post('user/project/timeframes', [TimeframesController::class, 'store']);
            Route::put('user/project/timeframes/{id}', [TimeframesController::class, 'update']);
            Route::delete('user/project/timeframes/{id}', [TimeframesController::class, 'destroy']);

            Route::get('user/friendships', [FriendshipsController::class, 'show_associated']);
            Route::post('user/friendships', [FriendshipsController::class, 'store']);
            Route::put('user/friendships/accept/{id}', [FriendshipsController::class, 'accept']);
            Route::delete('user/friendships/{id}', [FriendshipsController::class, 'destroy']);

            Route::get('user/preferences', [PreferencesController::class, 'show_associated']);
            Route::post('user/preferences/{project_id}', [PreferencesController::class, 'store']);
            Route::delete('user/preferences/{id}', [PreferencesController::class, 'destroy']);
        });

        Route::get('events', [EventsController::class, 'index']);
        Route::post('events', [EventsController::class, 'store']);
        Route::put('events/{id}', [EventsController::class, 'update']);
        Route::patch('events/{id}', [EventsController::class, 'syncPermissions']);
        Route::delete('events/{id}', [EventsController::class, 'destroy']);
    });

    Route::get('user', function () {
        return response()->json(['user' => Auth::user()]);
    });
    Route::get('user/permissions', function () {
        return response()->json(['permissions' => Auth::user()->getAllPermissions()->pluck('name')]);
    });
    Route::get('events/permissions', function () {
        $event = Event::where('from', '<=', date("Y-m-d"))->where('until', '>=', date("Y-m-d"))->first();

        if ($event) {
            return response()->json(['permissions' => $event->getAllPermissions()->pluck('name')->merge(collect(config('schedule.basic_permissions', [])))]);
        } else {
            return response()->json(['permissions' => config('schedule.basic_permissions', [])]);
        }
    });
});

Route::get('/authenticated', function () {
    return response()->json(['authenticated' => Auth::check(), 'user' => Auth::user()]);
});

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::get('/email/verified', function () {
    return response()->json(['email_verified_at' => Auth::user()->email_verified_at]);
});

Route::get('lang/{lang}', [LanguageController::class, 'switchLang']);
