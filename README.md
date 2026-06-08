# Syriable Filament Utilities

[![Latest Version on Packagist](https://img.shields.io/packagist/v/syriable/filament-utilities.svg?style=flat-square)](https://packagist.org/packages/syriable/filament-utilities)
[![GitHub Tests Action Status](https://github.com/syriable/filament-utilities/actions/workflows/tests.yml/badge.svg?branch=5.x)](https://github.com/syriable/filament-utilities/actions?query=workflow%3Atests+branch%3A5.x)
[![GitHub Code Style Action Status](https://github.com/syriable/filament-utilities/actions/workflows/fix-code-style.yml/badge.svg?branch=5.x)](https://github.com/syriable/filament-utilities/actions?query=workflow%3Afix-code-style+branch%3A5.x)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Developer tooling for the Syriable Filament ecosystem. The package wires custom Artisan generators that scaffold **translatable** Filament resources and module plugins, built on top of [`syriable/filament-translator`](https://github.com/syriable/filament-translator).

## Features

- **`syriable:make-resource`** — drop-in replacement for Filament's resource generator that extends `TranslatableResource` and translatable resource pages instead of Filament's base classes.
- **Module-aware model discovery** — interactive model selection searches Eloquent models under your `modules/` directory.
- **`plugin:resource`** — scaffolds a Filament panel plugin for an [InterNACHI/modular](https://github.com/InterNACHI/modular) module and registers it in the module service provider.
- **Custom file generators** — binds Syriable generators into Filament's `make:filament-resource` pipeline so generated code is translation-ready out of the box.

## Requirements

- PHP 8.4+
- Laravel 12 or 13
- Filament 5.5+
- [`syriable/filament-translator`](https://github.com/syriable/filament-translator) ^1.1
- [`syriable/filament-activitylog`](https://github.com/syriable/filament-activitylog) ^0.1

For `plugin:resource`, your application must use [InterNACHI/modular](https://github.com/InterNACHI/modular) with modules under the path configured in `config/app-modules.php`.

## Installation

Install the package via Composer:

```bash
composer require syriable/filament-utilities
```

Register [`TranslatorPlugin`](https://github.com/syriable/filament-translator) on every Filament panel that should resolve convention-based labels:

```php
use Filament\Panel;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            TranslatorPlugin::make(),
        ]);
}
```

`UtilitiesServiceProvider` is auto-discovered. No panel plugin registration is required for the generators to work.

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

### Generate a module Filament plugin

Scaffold a Filament plugin class inside a modular application:

```bash
php artisan plugin:resource users
```

When the module name is omitted, the command interactively lists directories under `modules/`.

The command:

1. Creates `{Module}Plugin.php` in `modules/{module}/src/` using the published stub.
2. Registers the plugin on the module's service provider via `Panel::configureUsing()`.

The generated plugin discovers resources, pages, and widgets under the module's `Filament/` directories.

### Publish generator stubs

Customize the plugin stub before running `plugin:resource`:

```bash
php artisan vendor:publish --tag=filament-utilities-stubs
```

Stubs are copied to `stubs/filament-utilities/` in your application root.

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
composer analyse   # PHPStan
composer lint      # Laravel Pint
```

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
