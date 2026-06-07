<?php

namespace Syriable\Filament\Plugins\Utilities\Commands;

use Filament\Commands\MakeResourceCommand as BaseMakeResourceCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\search;
use function Laravel\Prompts\suggest;

#[AsCommand(name: 'syriable:make-resource', aliases: [
    'syriable:make-resource',
    'syriable:resource',
])]
class MakeResourceCommand extends BaseMakeResourceCommand
{
    protected $description = 'Create a new Syriable resource class and default page classes';

    protected $name = 'syriable:make-resource';

    /**
     * @var array<string>
     */
    protected $aliases = [
        'syriable:make-resource',
        'syriable:resource',
    ];

    protected function configureModel(): void
    {
        if (filled($this->argument('model'))) {
            $this->modelFqnEnd = (string) str((string) $this->argument('model'))
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->when(
                    fn (Stringable $model): bool => str($model)->endsWith('Resource'),
                    fn (Stringable $model): Stringable => str($model)->beforeLast('Resource'),
                )
                ->studly()
                ->replace('/', '\\');

            if (blank($this->modelFqnEnd)) {
                $this->modelFqnEnd = 'Resource';
            }

            $modelNamespace = (string) ($this->option('model-namespace') ?? app()->getNamespace() . 'Models');

            /** @var class-string<Model> $modelFqn */
            $modelFqn = "{$modelNamespace}\\{$this->modelFqnEnd}";
            $this->modelFqn = $modelFqn;
        } else {
            $modelFqns = $this->discoverModelClasses(parentClass: Model::class);

            /** @var class-string<Model> $modelFqn */
            $modelFqn = suggest(
                label: 'What is the model?',
                options: function (string $search) use ($modelFqns): array {
                    $search = str($search)->trim()->replace(['\\', '/'], '');

                    if (blank($search)) {
                        return $modelFqns;
                    }

                    return array_filter(
                        $modelFqns,
                        fn (string $class): bool => str($class)->replace(['\\', '/'], '')->contains($search, ignoreCase: true),
                    );
                },
                placeholder: app()->getNamespace() . 'Models\\BlogPost',
                required: true,
            );
            $this->modelFqn = $modelFqn;

            $this->modelFqnEnd = class_basename($this->modelFqn);
        }

        if ($this->option('model')) {
            $this->callSilently('make:model', [
                'name' => $this->modelFqn,
            ]);
        }

        if ($this->option('migration')) {
            $table = (string) str($this->modelFqn)
                ->classBasename()
                ->pluralStudly()
                ->snake();

            $this->call('make:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
            ]);
        }

        if ($this->option('factory')) {
            $this->callSilently('make:factory', [
                'name' => $this->modelFqnEnd,
            ]);
        }
    }

    /**
     * @return array{string, string}
     */
    public function getResourcesLocation(string $question): array
    {
        if ($this->panel === null) {
            return parent::getResourcesLocation($question);
        }

        $directories = $this->panel->getResourceDirectories();
        $namespaces = $this->panel->getResourceNamespaces();

        foreach ($directories as $index => $directory) {
            if (str($directory)->startsWith(base_path('vendor')) || str($directory)->startsWith(base_path('app'))) {
                unset($directories[$index]);
                unset($namespaces[$index]);
            } else {
                $directories[$index] = str($directory)->replace('Filament', 'Syriable')->toString();
                $namespaces[$index] = str($namespaces[$index])->replace('Filament', 'Syriable')->toString();
            }
        }

        if ($this->option('resource-namespace')) {
            $resourceNamespace = (string) $this->option('resource-namespace');

            return [
                $resourceNamespace,
                $directories[array_search($resourceNamespace, $namespaces, true)],
            ];
        }

        $keyedNamespaces = array_combine(
            $namespaces,
            $namespaces,
        );

        /** @var string $namespace */
        $namespace = search(
            label: $question,
            options: function (?string $search) use ($keyedNamespaces): array {
                if (blank($search)) {
                    return $keyedNamespaces;
                }

                $search = str($search)->trim()->replace(['\\', '/'], '');

                return array_filter($keyedNamespaces, fn (string $namespace): bool => str($namespace)->replace(['\\', '/'], '')->contains($search, ignoreCase: true));
            },
        );

        return [
            $namespace,
            $directories[array_search($namespace, $namespaces, true)],
        ];
    }

    protected function configureLocation(): void
    {
        if ($this->hasResourceClassesOutsideDirectories) {
            $this->fqnEnd = "{$this->modelFqnEnd}Resource";
        } else {
            $this->fqnEnd = Str::pluralStudly($this->modelFqnEnd) . '\\' . class_basename($this->modelFqn) . 'Resource';
        }

        /** @var class-string $fqn */
        $fqn = $this->resourcesNamespace . '\\' . $this->fqnEnd;
        $this->fqn = $fqn;

        if ($this->hasResourceClassesOutsideDirectories) {
            $this->namespace = $this->fqn;
            $this->directory = (string) str("{$this->resourcesDirectory}/{$this->fqnEnd}")
                ->replace('\\', '/')
                ->replace('//', '/');
        } else {
            $this->namespace = (string) str($this->fqn)
                ->beforeLast('\\');
            $this->directory = (string) str($this->resourcesDirectory . '/' . Str::pluralStudly($this->modelFqnEnd))
                ->replace('\\', '/')
                ->replace('//', '/');
        }
    }

    /**
     * @param  class-string<Model>|null  $parentClass
     * @return list<class-string<Model>>
     */
    protected function discoverModelClasses(?string $parentClass = null, string $packageName = 'modules'): array
    {
        $classLoader = require base_path('vendor/autoload.php');

        /** @var array<class-string<Model>, string> $classMap */
        $classMap = $classLoader->getClassMap();

        $classes = [];

        foreach ($classMap as $class => $file) {
            if (blank($packageName) || blank($parentClass)) {
                continue;
            }

            if (! (str($file)->contains(DIRECTORY_SEPARATOR . $packageName . DIRECTORY_SEPARATOR) ||
                str($file)->contains('/' . $packageName . '/') ||
                str($file)->contains('\\' . $packageName . '\\'))) {
                continue;
            }

            if (! is_subclass_of($class, $parentClass)) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }
}
