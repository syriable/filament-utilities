<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Syriable\Filament\Plugins\Activitylog\Activitylog;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;

class UtilitiesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-utilities';
    }

    public function register(Panel $panel): void
    {
        $panel->plugins([
            Activitylog::make(),
            TranslatorPlugin::make()
                ->createMissingTranslationKeys()
                ->pathAliases([
                    'App\\Livewire' => 'livewire',
                    'Modules\\Users\\Filament\\Resources' => 'modules/users',
                ]),
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
