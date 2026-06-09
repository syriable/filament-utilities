<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Translator Plugin
    |--------------------------------------------------------------------------
    |
    | This is the translator plugin configuration.
    |
    */
    'translator' => [
        /*
        |--------------------------------------------------------------------------
        | Create Missing Translation Keys
        |--------------------------------------------------------------------------
        |
        | This is the create missing translation keys configuration.
        | If true, the translator plugin will create missing translation keys.
        |
        */
        'create_missing_translation_keys' => true,

        /*
        |--------------------------------------------------------------------------
        | Path Aliases
        |--------------------------------------------------------------------------
        |
        | This is the path aliases configuration.
        | Keys are the namespace and the value is the path alias.
        | Example:
        | 'App\\Livewire' => 'livewire',
        | 'Modules\\Users\\Filament\\Resources' => 'modules/users',
        | If empty, the translator plugin will not create path aliases.
        */
        'path_aliases' => [
            'App\\Livewire' => 'livewire',
            'Modules\\Users\\Filament\\Resources' => 'modules/users',
        ],
    ],
];
