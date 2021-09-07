<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session()->has('apiLanguage') && array_key_exists(Session()->get('apiLanguage'), config('languages.supported'))) {
            App::setLocale(Session()->get('apiLanguage'));
        } else {
            App::setLocale(config('app.fallback_locale'));
        }
        return $next($request);
    }
}

