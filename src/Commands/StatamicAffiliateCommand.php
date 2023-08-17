<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Larsvg\StatamicAffiliate\Collections\AffiliateCollection;
use Larsvg\StatamicAffiliate\Collections\AfilliateItem;

class StatamicAffiliateCommand extends Command
{
    public $signature = 'statamic-affiliate-import';

    public $description = 'Import affiliate data';

    public function handle(): int
    {
        $url = 'https://files.channable.com/NZsY2CW84UUY7_Qbmd7t9Q==.csv';

        $items = new AffiliateCollection();

        $items[] = new AfilliateItem();
        $items[] = new AfilliateItem();
        $items[] = new AfilliateItem();
        $items[] = new AfilliateItem();
        $items[] = new AfilliateItem();

        $this->comment('All done');

        return self::SUCCESS;
    }
}
