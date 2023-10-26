<?php

namespace Larsvg\StatamicAffiliate\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Console\Command;
use Larsvg\StatamicAffiliate\Collections\AffiliateCollection;
use Larsvg\StatamicAffiliate\Collections\AfilliateItem;

class StatamicAffiliateCommand extends Command
{
    public $signature = 'app:statamic-affiliate-import';

    public $description = 'Import affiliate data';

    public function handle(): int
    {
        $this->comment('Importing affiliate data');

        $client = new Client();
        $response = $client->get(config('statamic-affiliate.awin_import_feed'));

        if ($response->getStatusCode() === 200) {
            // Get the gzipped content and create a stream.
            $stream = Utils::streamFor(gzdecode($response->getBody()));

            // Now, you can read the CSV data from the stream.
            $csvData = $stream->getContents();

            dd($csvData);

            // Process the CSV data as needed.
        }


//        $items = new AffiliateCollection();
//
//        $items[] = new AfilliateItem();
//        $items[] = new AfilliateItem();
//        $items[] = new AfilliateItem();
//        $items[] = new AfilliateItem();
//        $items[] = new AfilliateItem();

        $this->comment('All done');

        return self::SUCCESS;
    }
}
