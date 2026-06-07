<?php

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

    protected string $pluginNamespace;

    protected string $moduleNameOriginal;

    /**
     * @return array<InputArgument>
     */
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

        $namespace = str($this->pluginFqn)->studly()->prepend(config('app-modules.modules_namespace') . '\\')->toString();

        $pluginPath = base_path(config('app-modules.modules_directory')) . '/' . $this->moduleNameOriginal . '/src/' . $plugin . '.php';

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
        $serviceProviderPath = base_path(config('app-modules.modules_directory')) . '/' . $this->moduleNameOriginal . '/src/Providers/' . $moduleName . 'ServiceProvider.php';

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

        // Check if Panel::configureUsing exists
        if (! str_contains($content, 'Panel::configureUsing')) {
            // Add Panel import if not exists
            if (! str_contains($content, 'use Filament\\Panel;')) {
                $content = str_replace(
                    'use Illuminate\\Support\\ServiceProvider;',
                    "use Illuminate\\Support\\ServiceProvider;\nuse Filament\\Panel;",
                    $content
                );
            }

            // Add plugin import
            $pluginImport = "use {$namespace}\\{$pluginClass};";
            if (! str_contains($content, $pluginImport)) {
                // Find the last use statement and add after it
                preg_match_all('/^use .+;$/m', $content, $matches);
                if ($matches[0] !== []) {
                    $lastUse = end($matches[0]);
                    $content = str_replace(
                        $lastUse,
                        $lastUse . "\n" . $pluginImport,
                        $content
                    );
                } else {
                    // Add after namespace if no use statements
                    $content = preg_replace(
                        '/^(namespace .+;)$/m',
                        "$1\n\n" . $pluginImport,
                        $content
                    );
                }
            }

            // Add Panel::configureUsing in register method
            $content = preg_replace(
                '/(public function register\(\): void\s*\{)/',
                "$1\n        Panel::configureUsing(function (Panel \$panel): void {\n            \$panel->plugin({$pluginClass}::make());\n        });\n",
                (string) $content
            );
        } else {
            // Panel::configureUsing exists, add plugin inside it
            // Add plugin import
            $pluginImport = "use {$namespace}\\{$pluginClass};";
            if (! str_contains($content, $pluginImport)) {
                preg_match_all('/^use .+;$/m', $content, $matches);
                if ($matches[0] !== []) {
                    $lastUse = end($matches[0]);
                    $content = str_replace(
                        $lastUse,
                        $lastUse . "\n" . $pluginImport,
                        $content
                    );
                }
            }

            // Add plugin registration inside Panel::configureUsing
            $content = preg_replace(
                '/(Panel::configureUsing\(function \(Panel \$panel\): void \{)/',
                "$1\n            \$panel->plugin({$pluginClass}::make());",
                (string) $content
            );
        }

        File::put($serviceProviderPath, (string) $content);
        $this->info('Plugin registration added to service provider.');
    }

    protected function configurePlugin(): void
    {
        if (filled($this->argument('plugin'))) {
            $originalName = (string) str((string) $this->argument('plugin'))
                ->trim('/')
                ->trim('\\')
                ->trim(' ');

            $this->moduleNameOriginal = $originalName;
            $this->pluginFqn = (string) str($originalName)
                ->studly()
                ->replace('/', '\\');
            $this->pluginNamespace = app()->getNamespace() . 'Plugins\\' . $this->pluginFqn;
        } else {
            $pluginFqns = collect(File::glob(base_path('modules/*')))
                ->map(fn ($path) => str($path)->after(base_path('modules/'))->toString())->toArray();

            $selected = suggest(
                label: 'What is the plugin?',
                options: function (string $search) use ($pluginFqns): array {
                    $search = str($search)->trim()->replace(['\\', '/'], '');

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
            $this->pluginFqn = (string) str($selected)->studly()->toString();
        }
    }
}
