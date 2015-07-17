<?php

return [
    'ignored_tables' => [
        'migrations',
    ],

    'pivot_tables' => [
        //
    ],

    'forced_names' => [
        //
    ],

    'templates_dir' => base_path().'/templates',

    'models' => [
        'dir'      => app_path('Models'),
        'ns'       => 'App\Models',
        'generate' => true,
    ],

    'repositories' => [
        'dir'      => app_path('Repositories'),
        'ns'       => 'App\Repositories',
        'generate' => true,
    ],

    'contracts' => [
        'dir'      => app_path('Contracts/Repositories'),
        'ns'       => 'App\Contracts\Repositories',
        'generate' => true,
    ],

    'facades' => [
        'dir'      => app_path('Facades/Repositories'),
        'ns'       => 'App\Facades\Repositories',
        'generate' => true,
    ]
];
