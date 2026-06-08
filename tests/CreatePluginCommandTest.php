<?php

use Syriable\Filament\Plugins\Utilities\Commands\CreatePluginCommand;

it('adds a Panel::configureUsing block to a provider that has none', function () {
    $content = <<<'PHP'
<?php

namespace Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
}
PHP;

    $result = CreatePluginCommand::injectPluginRegistration($content, 'Modules\\Users', 'UsersPlugin');

    expect($result)
        ->toContain('use Filament\Panel;')
        ->toContain('use Modules\Users\UsersPlugin;')
        ->toContain('Panel::configureUsing(function (Panel $panel): void {')
        ->toContain('$panel->plugin(UsersPlugin::make());');
});

it('appends the plugin to an existing Panel::configureUsing block', function () {
    $content = <<<'PHP'
<?php

namespace Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Panel;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            //
        });
    }
}
PHP;

    $result = CreatePluginCommand::injectPluginRegistration($content, 'Modules\\Users', 'UsersPlugin');

    expect(substr_count($result, 'Panel::configureUsing'))->toBe(1)
        ->and($result)->toContain('use Modules\Users\UsersPlugin;')
        ->and($result)->toContain('$panel->plugin(UsersPlugin::make());');
});

it('does not duplicate an import that is already present', function () {
    $content = <<<'PHP'
<?php

namespace Modules\Users\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Panel;
use Modules\Users\UsersPlugin;

class UsersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Panel::configureUsing(function (Panel $panel): void {
            //
        });
    }
}
PHP;

    $result = CreatePluginCommand::injectPluginRegistration($content, 'Modules\\Users', 'UsersPlugin');

    expect(substr_count($result, 'use Modules\Users\UsersPlugin;'))->toBe(1);
});
