<?php

namespace Larsvg\StatamicAffiliate\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Console\Command;
use Larsvg\StatamicAffiliate\Collections\AffiliateCollection;
use Larsvg\StatamicAffiliate\Collections\AfilliateItem;
use Statamic\Entries\Entry;

class StatamicAffiliateCommand extends Command
{
    public $signature = 'app:statamic-affiliate-import';

    public $description = 'Import affiliate data';

    public function handle(): int
    {
        $this->comment('Importing affiliate data');

        $affiliateItems = new AffiliateCollection;
        $affiliateItems->feedName = 'awin';
        $client = new Client();
        $response = $client->get(config('statamic-affiliate.awin_import_feed'));

        if ($response->getStatusCode() === 200) {
            $stream = Utils::streamFor(gzdecode($response->getBody()));
            $csvData = $stream->getContents();
            $items = explode("\n", $csvData);

            //dump(str_getcsv($items[0]));

            unset($items[0]);

            foreach ($items as $key => $line) {
                $properties = str_getcsv($line);

                if (! isset($properties[1])) {
                    continue;
                }

                $affiliateItems[] = new AfilliateItem(
                    productId: $properties[2],
                    merchantId: $properties[3],
                    afilliateLink: $properties[0],
                    productName: $properties[1],
                    productDescription: $properties[5],
                    price: (float) $properties[7],
                    deliveryCost: (int) empty($properties[15]) ? 0 : $properties[15],
                    image: $properties[12],
                    stock: 0
                );
            }
        }

        $this->importFeed($affiliateItems);
        $this->removeOldProducts($affiliateItems);

        $productCount = Entry::query()
            ->where('collection', 'products')
            ->count();

        $this->comment('Total '.$productCount.' products');

        return self::SUCCESS;
    }

    private function importFeed(AffiliateCollection $affiliateCollection)
    {
        foreach ($affiliateCollection as $item) {
            $entry = Entry::query()
                ->where('collection', 'products')
                ->where('product_id', $item->productId)
                ->where('merchant_id', $item->merchantId)
                ->first();

            if (empty($entry)) {
                $entry = new Entry();
                $entry->collection('products');
                $entry->set('product_id', $item->productId);
                $entry->set('merchant_id', $item->merchantId);
            }

            $entry->set('affiliate_link', $item->afilliateLink);
            $entry->set('batch_id', $affiliateCollection->batchId);
            $entry->set('title', $item->productName);
            $entry->set('product_description', $item->productDescription);
            $entry->set('price', $item->price);
            $entry->set('delivery_cost', $item->deliveryCost);
            $entry->set('image', $item->image);
            $entry->set('stock', $item->stock);
            $entry->set('feed_name', $affiliateCollection->feedName);
            $entry->save();
        }
    }

    private function removeOldProducts(AffiliateCollection $affiliateCollection): void
    {
        $entries = Entry::query()
            ->where('collection', 'products')
            ->where('batch_id', '!=', $affiliateCollection->batchId)
            ->where('feed_name', $affiliateCollection->feedName)
            ->get();

        foreach ($entries as $entry) {
            $entry->delete();
        }
    }
}
