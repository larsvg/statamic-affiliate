<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Larsvg\StatamicAffiliate\Collections\AffiliateCollection;
use Statamic\Entries\Entry;

abstract class StatamicAffiliateCommand extends Command
{
    private AffiliateCollection $affiliateCollection;

    private string $feedName;

    abstract public function setFeedName(): string;

    abstract public function CollectAffiliateItems(): AffiliateCollection;

    public function handle(): int
    {
        $this->feedName = $this->setFeedName();
        $this->comment('Importing '.$this->feedName.' affiliate data');

        $this->affiliateCollection = $this->CollectAffiliateItems();
        $this->importFeed();
        $itemsRemoved = $this->cleanupItemsNotInFeed();
        $this->comment($this->affiliateCollection->count().' items imported, '.$itemsRemoved.' items removed');

        return self::SUCCESS;
    }

    protected function importFeed(): void
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

            $image = $this->uploadImage($item);

            $entry->slug(Str::slug($item->productName));
            $entry->set('affiliate_link', $item->afilliateLink);
            $entry->set('batch_id', $this->affiliateCollection->batchId);
            $entry->set('title', $item->productName);
            $entry->set('product_description', $item->productDescription);
            $entry->set('price', $item->price);
            $entry->set('delivery_cost', $item->deliveryCost);
            $entry->set('image', $item->image);
            $entry->set('stock', $item->stock);
            $entry->set('feed_name', $this->feedName);
            $entry->set('responsive', [
                'src' => str_replace('images/', '', $image),
            ]);
            $entry->save();
        }
    }

    private function cleanupItemsNotInFeed(): int
    {
        $entries = Entry::query()
            ->where('collection', 'products')
            ->where('batch_id', '!=', $this->affiliateCollection->batchId)
            ->where('feed_name', $this->feedName)
            ->get();

        foreach ($entries as $entry) {
            $entry->delete();
        }

        return $entries->count();
    }

    protected function uploadImage($item): string
    {
        $directory = 'images/affiliate/'.$this->feedName;
        $file = $directory.'/'.$item->productId.'.jpg';

        if (File::exists(public_path($file))) {
            return $file;
        }

        if (! File::isDirectory(public_path($directory))) {
            File::makeDirectory(public_path($directory), 0755, true, true);
        }

        File::put(
            public_path($file),
            file_get_contents($item->image)
        );

        return $file;
    }
}
