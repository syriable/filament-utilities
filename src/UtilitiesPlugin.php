<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Syriable\Filament\Plugins\Activitylog\Activitylog;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\RoleResource;

class UtilitiesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-utilities';
    }

    public function register(Panel $panel): void
    {
        /** @var bool $createMissingTranslationKeys */
        $createMissingTranslationKeys = config('filament-utilities.translator.create_missing_translation_keys', true);

        /** @var array<string, string> $pathAliases */
        $pathAliases = config('filament-utilities.translator.path_aliases', []);

        $panel
            ->resources([
                RoleResource::class,
            ])
            ->plugins([
                Activitylog::make(),
                TranslatorPlugin::make()
                    ->createMissingTranslationKeys($createMissingTranslationKeys)
                    ->pathAliases($pathAliases),
                FilamentShieldPlugin::make()
                    ->navigationIcon(''),
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
