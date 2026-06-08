<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities;

use BladeUI\Icons\Factory;
use CodeWithDennis\FilamentAdvancedComponents\Filament\Tables\Components\AdvancedTextColumn;
use Filament\Actions\Action;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceCreateRecordPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceEditRecordPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceListRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Syriable\Filament\Plugins\Activitylog\Filament\Infolists\Components\ActivitylogTimeline;
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
    }

    /**
     * Apply opinionated defaults to Filament components.
     *
     * This is opt-in: call it from your own service provider's boot() method,
     * e.g. UtilitiesServiceProvider::initializeFilamentComponents();
     */
    public static function initializeFilamentComponents(): void
    {
        Page::alignFormActionsEnd();
        TextInput::configureUsing(fn (TextInput $component): TextInput => $component->maxLength(255)->trim());
        Textarea::configureUsing(fn (Textarea $component): Textarea => $component->trim());
        Action::configureUsing(fn (Action $action): Action => $action->modalAlignment(Alignment::Start)->modalWidth('md')->modalFooterActionsAlignment(Alignment::Center));
        Schema::configureUsing(fn (Schema $schema): Schema => $schema->columns(['lg' => 1, 'xl' => 1]));

        // AdvancedTextColumn ships with the (optional) codewithdennis/filament-advanced-components
        // package, so only configure it when that package is installed.
        if (class_exists(AdvancedTextColumn::class)) {
            AdvancedTextColumn::configureUsing(fn (AdvancedTextColumn $column): AdvancedTextColumn => $column->url(fn (AdvancedTextColumn $column): ?string => match (true) {
                $column->getMailable() => 'mailto:' . $column->getState(),
                $column->getCallable() => 'tel:' . $column->getState(),
                $column->getWhatsAppable() => 'https://wa.me/' . $column->getState(),
                default => null,
            }));
        }
    }

    /**
     * Register convenience macros on Filament components.
     *
     * This is opt-in: call it from your own service provider's boot() method,
     * e.g. UtilitiesServiceProvider::microFilamentComponents();
     */
    public static function microFilamentComponents(): void
    {
        /** @phpstan-ignore-next-line */
        ToggleButtons::macro('fullWidth', fn ($width = '100%') => $this->extraAttributes(fn (): array => [
            'style' => sprintf(
                'width: %s; white-space: nowrap; overflow-x: clip;',
                /** @phpstan-ignore-next-line */
                $this->evaluate($width)
            ),
        ]));

        /** @phpstan-ignore-next-line */
        Section::macro('prime', fn () => $this->extraAttributes(fn (): array => [
            'class' => 'border border-gray-200 rounded-2xl p-1 bg-gray-50 dark:border-gray-700 dark:bg-gray-800',
        ]));

        /** @phpstan-ignore-next-line */
        Textarea::macro('counter', fn () => $this->fieldWrapperView('filament-utilities::filament.components.textarea')->extraAlpineAttributes(['x-init' => '$watch(\'state\',value => length = value?.length); length = state?.length ?? length']));
    }

    /**
     * Register an application SVG icon set with the Blade Icons factory.
     *
     * This is opt-in: call it from your own service provider's boot() method,
     * e.g. UtilitiesServiceProvider::configureFactoryIcons();
     */
    public static function configureFactoryIcons(): void
    {
        app()->afterResolving(Factory::class, function (Factory $factory): void {
            $factory->add('fluxwork', [
                'path' => resource_path('svg/icons'),
                'prefix' => 'flux',
            ]);
        });
    }

    /**
     * Apply opinionated defaults to the activity log timeline component.
     *
     * This is opt-in: call it from your own service provider's boot() method,
     * e.g. UtilitiesServiceProvider::configureActivitylogTimeline();
     */
    public static function configureActivitylogTimeline(): void
    {
        ActivitylogTimeline::configureUsing(fn (ActivitylogTimeline $activitylogTimeline): ActivitylogTimeline => $activitylogTimeline
            ->compact()
            ->itemIcons([
                'created' => 'heroicon-o-plus',
                'deleted' => 'heroicon-o-trash',
                'updated' => 'heroicon-o-pencil-square',
                'restored' => 'heroicon-o-arrow-path',
                'preparation:started' => 'heroicon-o-cog',
                'assigned:role' => 'heroicon-o-key',
                'mailed:welcome-email' => 'heroicon-o-envelope',
            ])
            ->itemIconColors([
                // 'created' => 'info',
                // 'deleted' => 'danger',
                // 'preparation:started' => 'success',
                // 'assigned:role' => 'info',
                // 'mailed:welcome-email' => 'gray',
            ])
        );
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
