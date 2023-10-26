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

        $client   = new Client();
        $response = $client->get(config('statamic-affiliate.awin_import_feed'));

        $affiliateItems = [];

        if ($response->getStatusCode() === 200) {
            $stream  = Utils::streamFor(gzdecode($response->getBody()));
            $csvData = $stream->getContents();
            $items   = explode("\n", $csvData);

            dump(str_getcsv($items[0]));

            unset($items[0]);

            foreach ($items as $key => $line) {
                $properties = str_getcsv($line);

                if (!isset($properties[1])) {
                    continue;
                }

                $affiliateItems[] = new AfilliateItem(
                    productId: $properties[2],
                    merchantId: $properties[3],
                    productName: $properties[1],
                    productDescription: $properties[5],
                    price: (float) $properties[7],
                    deliveryCost: (int) empty($properties[15]) ? 0 : $properties[15],
                    image: $properties[12],
                    stock: 0
                );
            }
        }

        dd(new AffiliateCollection($affiliateItems));

        $this->comment('All done');

        return self::SUCCESS;
    }

}
