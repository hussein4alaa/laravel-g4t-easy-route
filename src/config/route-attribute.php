<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Controllers Path
    |--------------------------------------------------------------------------
    |
    | Here you can define the path(s) where your Controllers are located.
    | It can be either a single string or an array of paths.
    | Example: base_path('app/Http/Controllers') 
    | or ['app/Http/Controllers', 'packages/Custom/Controllers']
    |
    */

    'controllers_path' => [app_path('Http/Controllers')],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Routes
    |--------------------------------------------------------------------------
    |
    | Enable caching of the route attributes to improve performance
    | in large projects. Recommended for production.
    |
    */

    'cache' => true,
];