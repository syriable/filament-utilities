<?php

declare(strict_types=1);

use Modules\Users\Filament\Resources\Admins\AdminResource;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\RoleResource;

return [
    /*
    |--------------------------------------------------------------------------
    | Shield Plugin
    |--------------------------------------------------------------------------
    |
    | This is the shield plugin configuration.
    |
    */
    'shield' => [
        /*
        |--------------------------------------------------------------------------
        | Resources
        |--------------------------------------------------------------------------
        |
        | This is the resources configuration.
        |
        */
        'resources' => [
            /*
            |--------------------------------------------------------------------------
            | Exclude Resources for 'web' Guard Users
            |--------------------------------------------------------------------------
            |
            | Users belonging to the 'web' guard cannot use these resources.
            |
            */
            'exclude' => [
                RoleResource::class,
                AdminResource::class,
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Policies
        |--------------------------------------------------------------------------
        |
        | This is the policies configuration.
        |
        */
        'policies' => [
            /*
            |--------------------------------------------------------------------------
            | Methods for 'web' Guard Users
            |--------------------------------------------------------------------------
            |
            | These methods are available to users belonging to the 'web' guard.
            |
            */
            'methods' => ['viewAny', 'view', 'create', 'update'],

        ],

    ],
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
