<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities;

use BladeUI\Icons\Factory;
use CodeWithDennis\FilamentAdvancedComponents\Filament\Tables\Components\AdvancedTextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Syriable\Filament\Plugins\Activitylog\Filament\Infolists\Components\ActivitylogTimeline;

class Utilities
{
    /**
     * Initialize the utilities.
     */
    public static function initialize(): void
    {
        self::initializeFilamentComponents();
        self::microFilamentComponents();
        self::configureFactoryIcons();
        self::configureActivitylogTimeline();
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
                $column->getMailable() => 'mailto:'.$column->getState(),
                $column->getCallable() => 'tel:'.$column->getState(),
                $column->getWhatsAppable() => 'https://wa.me/'.$column->getState(),
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
        $register = function (Factory $factory): void {
            $paths = [__DIR__.'/../resources/svg/icons'];

            $applicationIconsPath = function_exists('resource_path')
                ? resource_path('svg/icons')
                : null;

            if (
                is_string($applicationIconsPath)
                && $applicationIconsPath !== $paths[0]
                && is_dir($applicationIconsPath)
            ) {
                $paths[] = $applicationIconsPath;
            }

            $factory->add('fluxwork', [
                'paths' => $paths,
                'prefix' => 'flux',
            ]);
        };

        app()->afterResolving(Factory::class, $register);

        if (app()->resolved(Factory::class)) {
            $register(app(Factory::class));
        }
    }

    /**
     * Apply opinionated defaults to the activity log timeline component.
     *
     * This is opt-in: call it from your own service provider's boot() method,
     * e.g. UtilitiesServiceProvider::configureActivitylogTimeline();
     */
    public static function configureActivitylogTimeline(): void
    {
        ActivitylogTimeline::configureUsing(
            fn (ActivitylogTimeline $activitylogTimeline): ActivitylogTimeline => $activitylogTimeline
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
}
