<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Controller;

class VerificationController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Email Verification Controller
	|--------------------------------------------------------------------------
	|
	| This controller is responsible for handling email verification for any
	| user that recently registered with the application. Emails may also
	| be re-sent if the user didn't receive the original email message.
	|
	*/

	/**
	 * Where to redirect users after verification.
	 *
	 * @var string
	 */
	protected $redirectTo;

	/**
	 * Where to redirect users when they are already verified.
	 *
	 * @var string
	 */
	protected $redirectVerifiedTo;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
        $this->redirectTo = $this->redirectVerifiedTo = config(config('inpriparo.frontend') . '.protocol') . '://' . app('currentTenant')->domain . config(config('inpriparo.frontend') . '.verify_email_redirect_route');

		$this->middleware('auth')->except('verify');
		$this->middleware('signed')->only('verify');
		$this->middleware('throttle:6,1')->only('verify', 'resend');
	}

	/**
	 * Mark the authenticated user's email address as verified.
	 *
	 * @param Request $request
	 *
	 * @return RedirectResponse
     *
	 * @throws AuthorizationException
	 */
	public function verify(Request $request): RedirectResponse
    {
		if ($request->user() && $request->user() != $request->route('id')) {
			Auth::logout();
		}

		if (!$request->user()) {
			Auth::loginUsingId($request->route('id'), true);
		}

		if ($request->user()->hasVerifiedEmail()) {
            return redirect()->away($this->redirectVerifiedTo);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->away($this->redirectTo);
	}
}
