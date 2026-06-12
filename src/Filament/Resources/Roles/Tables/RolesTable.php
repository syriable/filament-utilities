<?php

namespace Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Tables;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\RoleResource;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $pivotTable = config('permission.table_names.model_has_roles');
                $rolesTable = config('permission.table_names.roles');
                $rolePivotKey = app(PermissionRegistrar::class)->pivotRole;
                $roleKey = $query->getModel()->getKeyName();

                $query->addSelect([
                    'users_count' => DB::table($pivotTable)
                        ->selectRaw('count(*)')
                        ->whereColumn("{$pivotTable}.{$rolePivotKey}", "{$rolesTable}.{$roleKey}"),
                ]);
            })
            ->columns([
                TextColumn::make('name')
                    ->weight(FontWeight::Medium)
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('team.name')
                    ->default('Global')
                    ->badge()
                    ->color(fn (mixed $state): string => str($state)->contains('Global') ? 'gray' : 'primary')
                    ->searchable()
                    ->visible(fn (): bool => RoleResource::shield()->isCentralApp() && Utils::isTenancyEnabled()),
                TextColumn::make('permissions_count')
                    ->badge()
                    ->counts('permissions')
                    ->color('primary'),
                TextColumn::make('users_count')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
