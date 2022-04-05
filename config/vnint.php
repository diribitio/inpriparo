<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vnint Configuration
    |--------------------------------------------------------------------------
    |
    | This laravel app is only the backend and requires a seperate frontend to
    | be fully functional. The frontend I designed specifically for that purpose
    | is called Vnint and these are the parameteres cercerning their connection.
    |
    | Note: If you want to use this API with a different frontend you will only
    | need to create a new config file with the matching credentials and then change
    | the parameter APP_FRONTEND in the .env file
    |
    */

    'baseUrl' => 'diribitio.digital',
    'protocol' => 'http',
    'default_lang' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Email Redirect Configuration
    |--------------------------------------------------------------------------
    |
    |
    |
    */

    'verify_email_redirect_route' => '',
    'reset_password_redirect_route' => '/#/reset-password',

];
