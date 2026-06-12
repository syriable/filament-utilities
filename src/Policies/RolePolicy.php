<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return filament()->isServing()
            ? $authUser->can('ViewAny:Role')
            : $authUser->can('ViewAny:Role');
    }

    public function view(AuthUser $authUser, Role $role): bool
    {
        return filament()->isServing()
            ? $authUser->can('View:Role')
            : $authUser->can('View:Role');
    }

    public function create(AuthUser $authUser): bool
    {
        return filament()->isServing()
            ? $authUser->can('Create:Role')
            : $authUser->can('Create:Role');
    }

    public function update(AuthUser $authUser, Role $role): bool
    {
        return filament()->isServing()
            ? $authUser->can('Update:Role')
            : $authUser->can('Update:Role');
    }

    public function delete(AuthUser $authUser, Role $role): bool
    {
        return filament()->isServing()
            ? $authUser->can('Delete:Role')
            : $authUser->can('Delete:Role');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return filament()->isServing()
            ? $authUser->can('DeleteAny:Role')
            : $authUser->can('DeleteAny:Role');
    }

    public function restore(AuthUser $authUser, Role $role): bool
    {
        return filament()->isServing()
            ? $authUser->can('Restore:Role')
            : $authUser->can('Restore:Role');
    }

    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        return filament()->isServing()
            ? $authUser->can('ForceDelete:Role')
            : $authUser->can('ForceDelete:Role');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return filament()->isServing()
            ? $authUser->can('ForceDeleteAny:Role')
            : $authUser->can('ForceDeleteAny:Role');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return filament()->isServing()
            ? $authUser->can('RestoreAny:Role')
            : $authUser->can('RestoreAny:Role');
    }

    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return filament()->isServing()
            ? $authUser->can('Replicate:Role')
            : $authUser->can('Replicate:Role');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return filament()->isServing()
            ? $authUser->can('Reorder:Role')
            : $authUser->can('Reorder:Role');
    }
}
