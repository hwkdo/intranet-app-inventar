<?php

namespace Hwkdo\IntranetAppInventar\Commands;

use Illuminate\Console\Command;

class IntranetAppInventarCommand extends Command
{
    public $signature = 'intranet-app-inventar';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
