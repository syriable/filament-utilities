<?php

namespace Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\Pages;

use BezhanSalleh\FilamentShield\Resources\Roles\Pages\EditRole as BaseEditRole;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesResourcePageLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\RoleResource;

class EditRole extends BaseEditRole implements TranslatesConventionally
{
    use ResolvesResourcePageLabels;

    protected static string $resource = RoleResource::class;

    protected function beforeValidate(): void
    {
        RoleResource::prunePermissionStateForGuard($this);
    }
}
