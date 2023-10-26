<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Larsvg\StatamicAffiliate\Collections\AffiliateCollection;
use Statamic\Entries\Entry;

abstract class StatamicAffiliateCommand extends Command
{
    private AffiliateCollection $affiliateCollection;

    abstract public function setAffiliateCollection(): AffiliateCollection;

    public function handle(): int
    {
        $this->comment('Importing affiliate data');

        $this->affiliateCollection = $this->setAffiliateCollection();

        $this->importFeed();
        $this->removeOldProducts();

        $productCount = Entry::query()
            ->where('collection', 'products')
            ->count();

        $this->comment('Total '.$productCount.' products');

        return self::SUCCESS;
    }

    private function importFeed(): void
    {
        foreach ($this->affiliateCollection as $item) {
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

            $entry->slug(Str::slug($item->productName));
            $entry->set('affiliate_link', $item->afilliateLink);
            $entry->set('batch_id', $this->affiliateCollection->batchId);
            $entry->set('title', $item->productName);
            $entry->set('product_description', $item->productDescription);
            $entry->set('price', $item->price);
            $entry->set('delivery_cost', $item->deliveryCost);
            $entry->set('image', $item->image);
            $entry->set('stock', $item->stock);
            $entry->set('feed_name', $this->affiliateCollection->feedName);
            $entry->save();
        }
    }

    private function removeOldProducts(): void
    {
        $entries = Entry::query()
            ->where('collection', 'products')
            ->where('batch_id', '!=', $this->affiliateCollection->batchId)
            ->where('feed_name', $this->affiliateCollection->feedName)
            ->get();

        foreach ($entries as $entry) {
            $entry->delete();
        }
    }
}
