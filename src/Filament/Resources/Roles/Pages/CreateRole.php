<?php

namespace Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Pages;

use BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole as BaseCreateRole;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesResourcePageLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\RoleResource;

class CreateRole extends BaseCreateRole implements TranslatesConventionally
{
    use ResolvesResourcePageLabels;

    protected static string $resource = RoleResource::class;

    protected function beforeValidate(): void
    {
        RoleResource::prunePermissionStateForGuard($this);
    }
}
