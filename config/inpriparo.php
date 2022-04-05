<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inpriparo Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for the inpriparo API.
    | This determines what actions the backend will allow and how they work,
    | it's main purpose is to make the app suitable for multiple purposes.
    | You are free to adjust these settings to best fit your purpose.
    |
    */

    'frontend' => env('APP_FRONTEND', 'vnint'),

    /*
     * This is the base url for all tenant routes. The tenant domains will be the
     * subdomains of this route, e.g. mytenant.yourapp.com -> yourapp.com is the base url.
     */

    'baseUrl' => 'diribitio.digital',

    'defaultApplicationSettings' => [
        'non_guest_email_domain' => '@s.school.com',
        'max_friends' => 2,
        'min_preferences' => 3,
        'max_preferences' => 5,
    ],

];
