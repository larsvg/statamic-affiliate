<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;

class StatamicAffiliateCommand extends Command
{
    public $signature = 'statamic-affiliate';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
