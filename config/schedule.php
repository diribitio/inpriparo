<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Basic permissions
    |--------------------------------------------------------------------------
    |
    | These permssions will be ignored by the schedule middleware, therefore never
    | blocking access based on the current event. Note that this will NOT disable
    | the check in the permissions middleware!
    |
    */

    'basic_permissions' => [
        'feedback.store',
        'events.index',
        'events.store',
        'events.update',
        'events.syncPermissions',
        'events.destroy',
        'users.index',
        'roles.index',
        'permissions.index'
    ],

];
