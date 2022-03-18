<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{

    public function switchLang($lang): JsonResponse
    {
        if (array_key_exists($lang, config('languages.supported'))) {
            Session::put('apiLanguage', $lang);
            return response()->json(['language'=>$lang]);
        } else {
            return response()->json(['message' => __('errors.languageNotSupported')], 406);
        }
    }
}
