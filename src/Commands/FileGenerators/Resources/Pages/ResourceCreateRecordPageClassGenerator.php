<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities\Commands\FileGenerators\Resources\Pages;

use Filament\Commands\FileGenerators\Resources\Pages\ResourceCreateRecordPageClassGenerator as BaseResourceCreateRecordPageClassGenerator;
use Filament\Resources\Pages\CreateRecord;
use Nette\PhpGenerator\PhpNamespace;
use Syriable\Filament\Plugins\Translator\Filament\Resources\Resource\Pages\TranslatableCreateRecord;

class ResourceCreateRecordPageClassGenerator extends BaseResourceCreateRecordPageClassGenerator
{
    protected function configureNamespace(PhpNamespace $namespace): void
    {
        parent::configureNamespace($namespace);

        $namespace->removeUse(CreateRecord::class);
        $namespace->addUse(TranslatableCreateRecord::class);
    }

    public function getExtends(): string
    {
        return TranslatableCreateRecord::class;
    }
}
