<?php

namespace Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Schemas;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use Livewire\Component as Livewire;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\RoleResource;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make()
                            ->key('details')
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament-shield::filament-shield.field.name'))
                                    ->unique(
                                        ignoreRecord: true, /** @phpstan-ignore-next-line */
                                        modifyRuleUsing: fn (Unique $rule): Unique => Utils::isTenancyEnabled() ? $rule->where(Utils::getTenantModelForeignKey(), Filament::getTenant()?->id) : $rule
                                    )
                                    ->required()
                                    ->maxLength(255),

                                Select::make('guard_name')
                                    ->options(function (): array {
                                        $guards = array_keys(config('auth.guards', []));

                                        return array_combine($guards, $guards) ?: [];
                                    })
                                    ->label(__('filament-shield::filament-shield.field.guard_name'))
                                    ->default('web')
                                    ->live()
                                    ->afterStateHydrated(fn (?string $state) => RoleResource::handleConfig($state))
                                    ->afterStateUpdated(function (?string $state, Set $set, Livewire $livewire): void {
                                        RoleResource::prunePermissionStateForGuard($livewire, $set);
                                        RoleResource::toggleSelectAllViaEntities($livewire, $set);
                                    }),

                                Select::make(config('permission.column_names.team_foreign_key'))
                                    ->label(__('filament-shield::filament-shield.field.team'))
                                    ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                                    /** @phpstan-ignore-next-line */
                                    ->default(Filament::getTenant()?->id)
                                    ->options(fn (): array => in_array(Utils::getTenantModel(), [null, '', '0'], true) ? [] : Utils::getTenantModel()::pluck('name', 'id')->toArray())
                                    ->visible(fn (): bool => RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled())
                                    ->dehydrated(fn (): bool => RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                                RoleResource::getSelectAllFormComponent(),

                            ])
                            ->columns([
                                'sm' => 2,
                                'lg' => 3,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                RoleResource::getShieldFormComponents(),
            ]);
    }
}
