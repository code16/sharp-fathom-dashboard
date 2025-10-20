<?php

namespace Code16\SharpFathomDashboard\Commands;

use Illuminate\Console\Command;

class SharpFathomDashboardCommand extends Command
{
    public $signature = 'sharp-fathom-dashboard';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
