<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Path
    |--------------------------------------------------------------------------
    |
    | This value is the directory where new modules will be live.
    |
    */

    'directory' => 'app/Modules',

    /*
    |--------------------------------------------------------------------------
    | Namespace for new modules
    |--------------------------------------------------------------------------
    |
    | Use this to set the namespace of any new module.
    |
    */

    'namespace' => 'App\\Modules',

    /*
    |--------------------------------------------------------------------------
    | Check checks
    |--------------------------------------------------------------------------
    |
    | This is used to map to the corresponding file type in your stubs. The
    | reason for this is to allow for dynamic loading of module components,
    | such as the service providers.
    |
    */

    'file_checks' => [
        'ServiceProvider' => '%class%ServiceProvider.php',
        'Route' => 'routes/%type%.php',
        'ModelFactory' => 'Database/Factories/%class%ModelFactory.php',
        'DatabaseSeeder' => 'Database/Seeds/%class%DatabaseSeeder.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Submodule Files
    |--------------------------------------------------------------------------
    |
    | Use this to list the files from the stubs directory that are used to
    | create a submodule. For special use cases where your submodule uses a
    | different version of the module file simple add that file to the
    | submodule directory in `resources/stubs/modules/submodule`.
    |
    */

    'submodule' => [
        'Database/Seeds/%class%DatabaseSeeder.php.stub',
        'Http/Controllers/%class%Controller.php.stub',
        'Models/%class%.php.stub',
        'Repositories/%class%Repository.php.stub',
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Modules
    |--------------------------------------------------------------------------
    |
    | This array is dynamically updated upon the addition of a new module.
    |
    */

    'modules' => [],
];
