<?php

namespace Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as BaseResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Once;
use Livewire\Component as Livewire;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesResourceLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Pages\CreateRole;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Pages\EditRole;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Pages\ListRoles;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Schemas\RoleForm;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Tables\RolesTable;

class RoleResource extends BaseResource implements TranslatesConventionally
{
    use ResolvesResourceLabels;

    /** @var array<string, mixed>|null */
    protected static ?array $defaultShieldConfig = null;

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<Section>|null
     */
    public static function getResourceEntitiesSchemaWithConfig(Get $get): ?array
    {
        static::handleConfig($get('guard_name'));

        return static::getResourceEntitiesSchema();
    }

    public static function getTabFormComponentForResources(): Component
    {
        return static::shield()->hasSimpleResourcePermissionView()
            ? static::getTabFormComponentForSimpleResourcePermissionsView()
            : Tab::make('resources')
                ->visible(fn (): bool => Utils::isResourceTabEnabled())
                ->badge(fn (Get $get): ?int => static::getResourceTabBadgeCount())
                ->schema([
                    Grid::make()
                        ->schema(fn (Get $get): ?array => static::getResourceEntitiesSchemaWithConfig($get))
                        ->columns(fn (): array|int|string => static::shield()->getGridColumns()),
                ]);
    }

    public static function handleConfig(?string $guardName): void
    {
        static::$defaultShieldConfig ??= [
            'filament-shield.policies.methods' => config('filament-shield.policies.methods'),
            'filament-shield.resources.exclude' => config('filament-shield.resources.exclude'),
        ];

        if ($guardName === 'web') {
            config([
                'filament-shield.policies.methods' => config('filament-utilities.shield.policies.methods'),
                'filament-shield.resources.exclude' => config('filament-utilities.shield.resources.exclude'),
            ]);
        } else {
            config(static::$defaultShieldConfig);
        }

        Once::flush();
    }

    public static function prunePermissionStateForGuard(Livewire $livewire, ?Set $set = null): void
    {
        /** @var array<string, mixed> $data */
        $data = data_get($livewire, 'data', []);

        static::handleConfig(is_string($data['guard_name'] ?? null) ? $data['guard_name'] : null);

        $reserved = array_filter([
            'name',
            'guard_name',
            'select_all',
            config('permission.column_names.team_foreign_key'),
        ]);

        $validFields = static::permissionFieldOptionsMap();

        foreach ($validFields as $field => $validKeys) {
            $current = $data[$field] ?? [];

            if (! is_array($current)) {
                continue;
            }

            $pruned = array_values(array_intersect($current, $validKeys));

            if ($set instanceof Set) {
                $set($field, $pruned);
            } else {
                data_set($livewire, "data.{$field}", $pruned);
            }
        }

        foreach (array_keys($data) as $key) {
            if (in_array($key, $reserved, true)) {
                continue;
            }
            if (isset($validFields[$key])) {
                continue;
            }
            if (! is_array($data[$key] ?? null)) {
                continue;
            }

            if ($set instanceof Set) {
                $set($key, []);
            } else {
                data_set($livewire, "data.{$key}", []);
            }
        }
    }

    /**
     * @return array<string, list<string>>
     */
    protected static function permissionFieldOptionsMap(): array
    {
        $map = [];

        foreach (FilamentShield::getResources() ?? [] as $entity) {
            $map[$entity['resourceFqcn']] = array_keys(
                static::getResourcePermissionOptions($entity)
            );
        }

        if (Utils::isPageTabEnabled()) {
            $map['pages_tab'] = array_keys(static::getPageOptions());
        }

        if (Utils::isWidgetTabEnabled()) {
            $map['widgets_tab'] = array_keys(static::getWidgetOptions());
        }

        if (Utils::isCustomPermissionTabEnabled()) {
            $map['custom_permissions_tab'] = array_keys(
                FilamentShield::getCustomPermissions(static::shield()->hasLocalizedPermissionLabels()) ?? []
            );
        }

        return $map;
    }
}
