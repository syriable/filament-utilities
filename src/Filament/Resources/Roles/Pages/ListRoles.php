<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Pages;

use BezhanSalleh\FilamentShield\Resources\Roles\Pages\ListRoles as BaseListRoles;
use Filament\Actions\CreateAction;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesResourcePageLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\RoleResource;

class ListRoles extends BaseListRoles implements TranslatesConventionally
{
    use ResolvesResourcePageLabels;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
