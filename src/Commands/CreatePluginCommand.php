<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\InputArgument;

use function Laravel\Prompts\suggest;

class CreatePluginCommand extends Command
{
    use CanManipulateFiles;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'plugin:resource';

    protected $description = 'Generate a new Filament resource for a plugin';

    protected string $pluginFqn;

    protected string $moduleNameOriginal;

    /**
     * @return array<InputArgument>
     */
    #[\Override]
    protected function getArguments(): array
    {
        return [
            new InputArgument(
                name: 'plugin',
                mode: InputArgument::OPTIONAL,
                description: 'The name of the plugin to generate the resource for',
            ),
        ];
    }

    public function handle(): int
    {
        $this->configurePlugin();

        $plugin = str($this->pluginFqn)->studly()->append('Plugin')->toString();

        $namespace = str($this->pluginFqn)->studly()->prepend($this->modulesNamespace() . '\\')->toString();

        $pluginPath = base_path($this->modulesDirectory()) . '/' . $this->moduleNameOriginal . '/src/' . $plugin . '.php';

        $this->copyStubToApp('plugin', $pluginPath, [
            'namespace' => $namespace,
            'class' => $plugin,
            'class_string' => str($this->pluginFqn)->studly()->lower()->toString(),
            'discover_resources' => (string) str($namespace)->append('\Filament\Resources')->replace('\\', '\\\\'),
            'discover_pages' => (string) str($namespace)->append('\Filament\Pages')->replace('\\', '\\\\'),
            'discover_widgets' => (string) str($namespace)->append('\Filament\Widgets')->replace('\\', '\\\\'),

        ]);

        // Automatically register plugin in service provider
        $this->registerPluginInServiceProvider($namespace, $plugin);

        $this->info('Plugin created successfully at: ' . $pluginPath);

        return self::SUCCESS;
    }

    protected function registerPluginInServiceProvider(string $namespace, string $pluginClass): void
    {
        $moduleName = str($this->moduleNameOriginal)->studly()->toString();
        $serviceProviderPath = base_path($this->modulesDirectory()) . '/' . $this->moduleNameOriginal . '/src/Providers/' . $moduleName . 'ServiceProvider.php';

        // Check if service provider exists
        if (! File::exists($serviceProviderPath)) {
            $this->warn("Service provider not found at: {$serviceProviderPath}");

            return;
        }

        $content = File::get($serviceProviderPath);

        // Check if plugin is already registered
        if (str_contains($content, $pluginClass . '::make()')) {
            $this->info('Plugin already registered in service provider.');

            return;
        }

        File::put($serviceProviderPath, self::injectPluginRegistration($content, $namespace, $pluginClass));
        $this->info('Plugin registration added to service provider.');
    }

    /**
     * Insert the plugin's import and registration into a module service provider's
     * source. When the provider has no `Panel::configureUsing()` block one is added
     * to its `register()` method; otherwise the plugin is appended to the existing block.
     */
    public static function injectPluginRegistration(string $content, string $namespace, string $pluginClass): string
    {
        $pluginImport = "use {$namespace}\\{$pluginClass};";

        if (! str_contains($content, 'Panel::configureUsing')) {
            // Add Panel import if not present.
            if (! str_contains($content, 'use Filament\\Panel;')) {
                $content = str_replace(
                    'use Illuminate\\Support\\ServiceProvider;',
                    "use Illuminate\\Support\\ServiceProvider;\nuse Filament\\Panel;",
                    $content
                );
            }

            $content = self::addUseStatement($content, $pluginImport);

            // Add Panel::configureUsing in the register method.
            return (string) preg_replace(
                '/(public function register\(\): void\s*\{)/',
                "$1\n        Panel::configureUsing(function (Panel \$panel): void {\n            \$panel->plugin({$pluginClass}::make());\n        });\n",
                $content
            );
        }

        // Panel::configureUsing already exists, add the plugin inside it.
        $content = self::addUseStatement($content, $pluginImport);

        return (string) preg_replace(
            '/(Panel::configureUsing\(function \(Panel \$panel\): void \{)/',
            "$1\n            \$panel->plugin({$pluginClass}::make());",
            $content
        );
    }

    /**
     * Append a `use` statement after the last existing import, or after the
     * namespace declaration when the file has no imports yet. No-ops when the
     * statement is already present.
     */
    protected static function addUseStatement(string $content, string $useStatement): string
    {
        if (str_contains($content, $useStatement)) {
            return $content;
        }

        // Tolerate CRLF line endings: in multiline mode `$` sits before `\n`, so a
        // trailing `\r` (e.g. on a Windows checkout) would otherwise break `;$`.
        preg_match_all('/^use .+;\r?$/m', $content, $matches);

        if ($matches[0] !== []) {
            $lastUse = end($matches[0]);

            return str_replace($lastUse, $lastUse . "\n" . $useStatement, $content);
        }

        return (string) preg_replace(
            '/^(namespace .+;)\r?$/m',
            "$1\n\n" . $useStatement,
            $content
        );
    }

    protected function configurePlugin(): void
    {
        $pluginArgument = $this->argument('plugin');

        if (is_string($pluginArgument) && filled($pluginArgument)) {
            $originalName = str($pluginArgument)
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->replaceMatches('/[^A-Za-z0-9_\/-]/', '')
                ->toString();

            $this->moduleNameOriginal = $originalName;
            $this->pluginFqn = str($originalName)
                ->studly()
                ->replace('/', '\\')
                ->toString();

            return;
        }

        $modulesDirectory = $this->modulesDirectory();

        $pluginFqns = collect(File::glob(base_path($modulesDirectory . '/*')))
            ->map(fn (string $path): string => str($path)->after(base_path($modulesDirectory . '/'))->toString())
            ->toArray();

        $selected = suggest(
            label: 'What is the plugin?',
            options: function (string $search) use ($pluginFqns): array {
                $search = str($search)->trim()->replace(['\\', '/'], '')->toString();

                if (blank($search)) {
                    return $pluginFqns;
                }

                return array_filter(
                    $pluginFqns,
                    fn (string $class): bool => str($class)->replace(['\\', '/'], '')->contains($search, ignoreCase: true),
                );
            },
            placeholder: 'users',
            required: true,
        );

        $this->moduleNameOriginal = $selected;
        $this->pluginFqn = str($selected)->studly()->toString();
    }

    /**
     * The application's modules directory, as configured by InterNACHI/modular.
     */
    protected function modulesDirectory(): string
    {
        return (string) config('app-modules.modules_directory', 'modules');
    }

    /**
     * The root namespace for the application's modules, as configured by InterNACHI/modular.
     */
    protected function modulesNamespace(): string
    {
        return (string) config('app-modules.modules_namespace', 'Modules');
    }
}
