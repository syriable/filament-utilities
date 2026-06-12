# Syriable Filament Utilities

[![Latest Version on Packagist](https://img.shields.io/packagist/v/syriable/filament-utilities.svg?style=flat-square)](https://packagist.org/packages/syriable/filament-utilities)
[![GitHub Tests Action Status](https://github.com/syriable/filament-utilities/actions/workflows/tests.yml/badge.svg?branch=5.x)](https://github.com/syriable/filament-utilities/actions?query=workflow%3Atests+branch%3A5.x)
[![GitHub Code Style Action Status](https://github.com/syriable/filament-utilities/actions/workflows/fix-code-style.yml/badge.svg?branch=5.x)](https://github.com/syriable/filament-utilities/actions?query=workflow%3Afix-code-style+branch%3A5.x)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Developer tooling for the Syriable Filament ecosystem. The package wires custom Artisan generators that scaffold **translatable** Filament resources and module plugins, a **guard-aware** Filament Shield role resource, and a panel plugin that bundles Syriable's translator, activity log, and Shield integrations.

## Features

- **`syriable:make-resource`** — drop-in replacement for Filament's resource generator that extends `TranslatableResource` and translatable resource pages instead of Filament's base classes.
- **Module-aware model discovery** — interactive model selection searches Eloquent models under your `modules/` directory.
- **`plugin:resource`** (`CreatePluginCommand`) — scaffolds a Filament panel plugin for an [InterNACHI/modular](https://github.com/InterNACHI/modular) module, auto-discovers its Filament components, and registers the plugin in the module service provider.
- **Custom file generators** — binds Syriable generators into Filament's `make:filament-resource` pipeline so generated code is translation-ready out of the box.
- **`UtilitiesPlugin`** — registers [`TranslatorPlugin`](https://github.com/syriable/filament-translator), [`Activitylog`](https://github.com/syriable/filament-activitylog), and [`FilamentShieldPlugin`](https://github.com/bezhanSalleh/filament-shield) on a panel from a single entry point.
- **Guard-aware `RoleResource`** — extends Filament Shield's role resource with a live `guard_name` selector, per-guard permission matrices, and correct user counts across morph types.

## Requirements

- PHP 8.4+
- Laravel 12 or 13
- Filament 5.5+
- [`bezhansalleh/filament-shield`](https://github.com/bezhanSalleh/filament-shield) ^4.2
- [`syriable/filament-translator`](https://github.com/syriable/filament-translator) ^1.1
- [`syriable/filament-activitylog`](https://github.com/syriable/filament-activitylog) ^0.1

For `plugin:resource`, your application must use [InterNACHI/modular](https://github.com/InterNACHI/modular) with modules under the path configured in `config/app-modules.php`.

## Installation

Install the package via Composer:

```bash
composer require syriable/filament-utilities
```

`UtilitiesServiceProvider` is auto-discovered. Register `UtilitiesPlugin` on every Filament panel that should use the bundled Syriable integrations:

```php
use Filament\Panel;
use Syriable\Filament\Plugins\Utilities\UtilitiesPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            UtilitiesPlugin::make(),
        ]);
}
```

`UtilitiesPlugin` registers the custom `RoleResource`, `TranslatorPlugin`, `Activitylog`, and `FilamentShieldPlugin`. You do not need to register those plugins separately when using `UtilitiesPlugin`.

## Usage

### Generate a translatable resource

Use `syriable:make-resource` (alias: `syriable:resource`) instead of `make:filament-resource`. It accepts the same options as Filament's command — panel selection, soft deletes, separate form/table schema classes, and so on.

```bash
php artisan syriable:make-resource Buyer --panel=dashboard
```

When no model argument is passed, the command interactively suggests Eloquent models discovered from classes loaded from your `modules/` directory.

Generated classes extend Syriable's translatable bases:

| Generated class | Extends |
| --- | --- |
| Resource | `TranslatableResource` |
| Create page | `TranslatableCreateRecord` |
| Edit page | `TranslatableEditRecord` |
| List page | `TranslatableListRecords` |

Model namespaces are resolved relative to the selected resource namespace so module resources reference module models instead of `App\Models`.

After generation, add translation keys under `lang/{locale}/` following the [filament-translator convention](https://github.com/syriable/filament-translator#translation-key-convention). Enable `createMissingTranslationKeys()` during local development to scaffold missing keys automatically.

### `plugin:resource` (`CreatePluginCommand`)

Scaffold a Filament panel plugin inside an [InterNACHI/modular](https://github.com/InterNACHI/modular) module:

```bash
php artisan plugin:resource users
```

| Argument | Required | Description |
| --- | --- | --- |
| `plugin` | No | Module directory name (for example, `users`). When omitted, the command interactively suggests directories from your configured modules path. |

The command reads `config('app-modules.modules_directory')` (default `modules`) and `config('app-modules.modules_namespace')` (default `Modules`) to resolve paths and namespaces.

**What it generates**

For `php artisan plugin:resource users`, the command creates:

```
modules/users/src/UsersPlugin.php
```

with namespace `Modules\Users`. The generated plugin class:

- implements Filament's `Plugin` contract,
- uses the module slug as its plugin ID (`users`),
- discovers resources, pages, and widgets under `Filament/Resources`, `Filament/Pages`, and `Filament/Widgets` relative to the plugin file.

**Service provider registration**

`CreatePluginCommand` also patches the module service provider at `modules/{module}/src/Providers/{Module}ServiceProvider.php`:

- adds the plugin `use` import when missing,
- appends `$panel->plugin(UsersPlugin::make())` to an existing `Panel::configureUsing()` block, or
- creates a new `Panel::configureUsing()` block inside `register()` when none exists.

Re-running the command is safe — it skips registration when the plugin is already present.

Customize the generated plugin by publishing the stub before running the command (see [Publish generator stubs](#publish-generator-stubs)).

### Guard-aware role management

The package replaces Filament Shield's default `RoleResource` with a translatable variant that supports multiple authentication guards.

When creating or editing a role, the `guard_name` field is populated from `config('auth.guards')` and updates the permission matrix live. Changing the guard:

- swaps Filament Shield's `resources.exclude` and `policies.methods` config for guard-specific overrides from `config/filament-utilities.php`,
- flushes Shield's cached resource discovery so the permission checkboxes reflect the selected guard,
- prunes stale checkbox state so validation does not fail when switching between guards.

The roles table counts users via the `model_has_roles` pivot directly, so `users_count` is accurate when the same role is assigned to models on different guards (for example, `Admin` and `Buyer`).

Publish and customize the web-guard overrides:

```bash
php artisan vendor:publish --tag=filament-utilities-config
```

```php
use Modules\Users\Filament\Resources\Admins\AdminResource;
use Syriable\Filament\Plugins\Utilities\Filament\Resources\Roles\RoleResource;

return [
    'shield' => [
        'resources' => [
            // Resources hidden from the permission matrix for the 'web' guard.
            'exclude' => [
                RoleResource::class,
                AdminResource::class,
            ],
        ],
        'policies' => [
            // Policy methods available in the permission matrix for the 'web' guard.
            'methods' => ['viewAny', 'view', 'create', 'update'],
        ],
    ],
    // ...
];
```

Non-`web` guards use your application's `config/filament-shield.php` values. Run `php artisan shield:generate` as usual to scaffold permissions and policies for application resources.

### Publish generator stubs

Customize the plugin stub before running `plugin:resource`:

```bash
php artisan vendor:publish --tag=filament-utilities-stubs
```

Stubs are copied to `stubs/filament-utilities/` in your application root.

### Configuration

Publish the config file to customize translator path aliases, missing-key scaffolding, and Shield guard overrides:

```bash
php artisan vendor:publish --tag=filament-utilities-config
```

This copies `config/filament-utilities.php` to your application's `config/` directory:

```php
return [
    'shield' => [
        'resources' => [
            'exclude' => [
                // Resource classes omitted from the 'web' guard permission matrix.
            ],
        ],
        'policies' => [
            'methods' => ['viewAny', 'view', 'create', 'update'],
        ],
    ],
    'translator' => [
        // Scaffold missing translation keys while resolving labels.
        'create_missing_translation_keys' => true,

        // Map namespaces to translation path aliases.
        'path_aliases' => [
            'App\\Livewire' => 'livewire',
            'Modules\\Users\\Filament\\Resources' => 'modules/users',
        ],
    ],
];
```

`UtilitiesPlugin` reads these values when registering `TranslatorPlugin`, `Activitylog`, `FilamentShieldPlugin`, and the bundled `RoleResource`.

### Opinionated Filament defaults and macros

The package ships several opt-in helpers. They are **not** applied automatically — call them from your own service provider's `boot()` method:

```php
use Syriable\Filament\Plugins\Utilities\UtilitiesServiceProvider;

public function boot(): void
{
    UtilitiesServiceProvider::initializeFilamentComponents();
    UtilitiesServiceProvider::microFilamentComponents();
    UtilitiesServiceProvider::configureFactoryIcons();
    UtilitiesServiceProvider::configureActivitylogTimeline();
}
```

`initializeFilamentComponents()` applies global defaults via `configureUsing()`:

- end-aligned form actions on pages,
- `TextInput` capped at 255 characters and trimmed,
- trimmed `Textarea`,
- consistent modal alignment/width for actions,
- a single-column responsive `Schema` default,
- when [`codewithdennis/filament-advanced-components`](https://filamentphp.com/plugins/codewithdennis-advanced-components) is installed, `AdvancedTextColumn` values become clickable `mailto:` / `tel:` / `https://wa.me/` links.

`microFilamentComponents()` registers convenience macros:

- `ToggleButtons::fullWidth()`,
- `Section::prime()` (a rounded, bordered container),
- `Textarea::counter()` — renders a live character counter using this package's `filament-utilities::filament.components.textarea` field wrapper.

`configureFactoryIcons()` registers your application's `resources/svg/icons` directory as a Blade Icons set (`fluxwork`, prefix `flux`).

`configureActivitylogTimeline()` applies compact defaults and a per-event icon map to [`syriable/filament-activitylog`](https://github.com/syriable/filament-activitylog)'s `ActivitylogTimeline` component.

> The `AdvancedTextColumn` integration requires the optional, paid
> [`codewithdennis/filament-advanced-components`](https://filamentphp.com/plugins/codewithdennis-advanced-components)
> package. It is guarded by `class_exists()`, so the rest of the defaults work without it.

## Testing

```bash
composer test
```

Other useful scripts:

```bash
composer analyse   # PHPStan (512M memory limit)
composer lint      # Laravel Pint
```

Run `composer analyse` instead of invoking PHPStan directly — the package analysis exceeds PHP's default 128M memory limit.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Syriable](https://github.com/syriable)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
