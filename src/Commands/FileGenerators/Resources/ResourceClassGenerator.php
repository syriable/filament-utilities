<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities\Commands\FileGenerators\Resources;

use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator as BaseResourceClassGenerator;
use Nette\PhpGenerator\PhpNamespace;
use Syriable\Filament\Plugins\Translator\Filament\Resources\TranslatableResource;

class ResourceClassGenerator extends BaseResourceClassGenerator
{
    #[\Override]
    public function getModelFqn(): string
    {
        $oldModel = parent::getModelFqn();

        $namespace = str($this->namespace->getName())->before('Filament')->toString();

        return str($oldModel)->replace('App\\', $namespace)->toString();
    }

    protected function configureNamespace(PhpNamespace $namespace): void
    {
        parent::configureNamespace($namespace);

        $namespace->removeUse(\Filament\Resources\Resource::class);
        $namespace->removeUse($this->modelFqn);
        $namespace->addUse(TranslatableResource::class);
        $namespace->addUse($this->getModelFqn());
    }

    #[\Override]
    public function getExtends(): string
    {
        return TranslatableResource::class;
    }

    #[\Override]
    public function getImplements(): array
    {
        return [];
    }
}
