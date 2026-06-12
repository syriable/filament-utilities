<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities;

use Filament\Commands\FileGenerators\Resources\Pages\ResourceCreateRecordPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceEditRecordPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceListRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Syriable\Filament\Plugins\Utilities\Facades\Utilities;
use Syriable\Filament\Plugins\Utilities\Testing\TestsUtilities;

class UtilitiesServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-utilities';

    public static string $viewNamespace = 'filament-utilities';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('syriable/filament-utilities');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        $this->app->bind(ResourceClassGenerator::class, Commands\FileGenerators\Resources\ResourceClassGenerator::class);
        $this->app->bind(ResourceEditRecordPageClassGenerator::class, Commands\FileGenerators\Resources\Pages\ResourceEditRecordPageClassGenerator::class);
        $this->app->bind(ResourceListRecordsPageClassGenerator::class, Commands\FileGenerators\Resources\Pages\ResourceListRecordsPageClassGenerator::class);
        $this->app->bind(ResourceCreateRecordPageClassGenerator::class, Commands\FileGenerators\Resources\Pages\ResourceCreateRecordPageClassGenerator::class);
        $this->app->singleton(Utilities::class, \Syriable\Filament\Plugins\Utilities\Utilities::class);
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-utilities/{$file->getFilename()}"),
                ], 'filament-utilities-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsUtilities);
        Utilities::initialize();
    }

    protected function getAssetPackageName(): string
    {
        return 'syriable/filament-utilities';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-utilities', __DIR__ . '/../resources/dist/components/filament-utilities.js'),
            // Css::make('filament-utilities-styles', __DIR__ . '/../resources/dist/filament-utilities.css'),
            // Js::make('filament-utilities-scripts', __DIR__ . '/../resources/dist/filament-utilities.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            Commands\UtilitiesCommand::class,
            Commands\MakeResourceCommand::class,
            Commands\CreatePluginCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_utilities_table',
        ];
    }
}
