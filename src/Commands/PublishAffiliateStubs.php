<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;

class PublishAffiliateStubs extends Command
{
    protected $signature   = 'affiliate:publish-stubs';
    protected $description = 'Publish the affiliate stubs.';

    public function handle()
    {
        dd('Publish the affiliate stubs.');
    }

}
