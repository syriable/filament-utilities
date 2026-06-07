<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities\Commands\FileGenerators\Resources\Pages;

use Filament\Commands\FileGenerators\Resources\Pages\ResourceListRecordsPageClassGenerator as BaseResourceListRecordsPageClassGenerator;
use Filament\Resources\Pages\ListRecords;
use Nette\PhpGenerator\PhpNamespace;
use Syriable\Filament\Plugins\Translator\Filament\Resources\Resource\Pages\TranslatableListRecords;

class ResourceListRecordsPageClassGenerator extends BaseResourceListRecordsPageClassGenerator
{
    #[\Override]
    public function getExtends(): string
    {
        return TranslatableListRecords::class;
    }

    protected function configureNamespace(PhpNamespace $namespace): void
    {
        parent::configureNamespace($namespace);

        $namespace->removeUse(ListRecords::class);
        $namespace->addUse(TranslatableListRecords::class);
    }
}
