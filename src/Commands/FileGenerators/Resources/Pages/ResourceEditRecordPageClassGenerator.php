<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities\Commands\FileGenerators\Resources\Pages;

use Filament\Commands\FileGenerators\Resources\Pages\ResourceEditRecordPageClassGenerator as BaseResourceEditRecordPageClassGenerator;
use Filament\Resources\Pages\EditRecord;
use Nette\PhpGenerator\PhpNamespace;
use Syriable\Filament\Plugins\Translator\Filament\Resources\Resource\Pages\TranslatableEditRecord;

class ResourceEditRecordPageClassGenerator extends BaseResourceEditRecordPageClassGenerator
{
    protected function configureNamespace(PhpNamespace $namespace): void
    {
        parent::configureNamespace($namespace);

        $namespace->removeUse(EditRecord::class);
        $namespace->addUse(TranslatableEditRecord::class);
    }

    public function getExtends(): string
    {
        return TranslatableEditRecord::class;
    }
}
