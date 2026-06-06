<?php

namespace Syriable\Filament\Plugins\Utilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Syriable\Filament\Plugins\Utilities\Utilities
 */
class Utilities extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Syriable\Filament\Plugins\Utilities\Utilities::class;
    }
}
