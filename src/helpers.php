<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;

use function Filament\Support\discover_app_classes;

if (! function_exists('discover_classes')) {
    /**
     * @return array<class-string>
     */
    function discover_classes(?string $parentClass): array
    {
        return discover_app_classes($parentClass);
    }
}

if (! function_exists('discover_package_classes')) {
    /**
     * Discover classes of a given type that live inside the application's modules
     * directory. When no package name is supplied it falls back to the directory
     * configured by InterNACHI/modular (`app-modules.modules_directory`).
     *
     * @param  class-string<Model>|null  $parentClass
     * @return list<class-string<Model>>
     */
    function discover_package_classes(?string $parentClass = null, ?string $packageName = null): array
    {
        $packageName = str((string) ($packageName ?? config('app-modules.modules_directory', 'modules')))
            ->afterLast('/')
            ->afterLast('\\')
            ->toString();

        if (blank($packageName) || blank($parentClass)) {
            return [];
        }

        $classLoader = require base_path('vendor/autoload.php');

        /** @var array<class-string<Model>, string> $classMap */
        $classMap = $classLoader->getClassMap();

        $classes = [];

        foreach ($classMap as $class => $file) {
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
