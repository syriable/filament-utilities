<?php

namespace Syriable\Filament\Plugins\Utilities\Commands;

use Illuminate\Console\Command;

class UtilitiesCommand extends Command
{
    public $signature = 'filament-utilities';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
