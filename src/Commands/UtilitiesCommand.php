<?php

declare(strict_types=1);

namespace Syriable\Filament\Plugins\Utilities\Commands;

use Illuminate\Console\Command;

class UtilitiesCommand extends Command
{
    public $signature = 'filament-utilities';

    public $description = 'Display a confirmation that filament-utilities is installed';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
