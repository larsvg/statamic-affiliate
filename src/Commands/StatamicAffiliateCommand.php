<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Larsvg\StatamicAffiliate\Collections\AffiliateCollection;
use Larsvg\StatamicAffiliate\Collections\AfilliateItem;
use Larsvg\StatamicAffiliate\Events\FeedImported;
use Statamic\Entries\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Taxonomies\LocalizedTerm;

abstract class StatamicAffiliateCommand extends Command
{
    private AffiliateCollection $affiliateCollection;

    private string $feedName;

    abstract public function setFeedName(): string;

    abstract public function CollectAffiliateItems(): AffiliateCollection;

    private array $created = [];
    private array $updated = [];
    private array $deleted = [];

    public function handle(): int
    {


        $this->feedName = $this->setFeedName();
        $this->comment('Importing ' . $this->feedName . ' affiliate data');

        $this->affiliateCollection = $this->CollectAffiliateItems();
        $this->importFeed();
        $this->cleanupItemsNotInFeed();

        $this->comment(count($this->updated) . ' updated, '. count($this->deleted) . ' deleted, '. count($this->created) . ' created');

        event(new FeedImported($this->created, $this->updated, $this->deleted));

        return self::SUCCESS;
    }

    protected function importFeed(): void
    {
        foreach ($this->affiliateCollection as $item) {
            $new   = false;
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

                $new = true;
            }

            $image = $this->uploadImage($item);

            $entry->slug(Str::slug($item->productName));
            $entry->set('affiliate_link', $item->afilliateLink);
            $entry->set('batch_id', $this->affiliateCollection->batchId);
            $entry->set('title', $item->productName);
            $entry->set('product_description', $item->productDescription);
            $entry->set('price', $item->price);
            $entry->set('delivery_cost', $item->deliveryCost);
            $entry->set('stock', $item->stock);
            $entry->set('feed_name', $this->feedName);
            $entry->set('image', $item->image);
            $entry->set('merchant_name', $item->merchantName);
            $entry->set('responsive', [
                'src' => str_replace('images/', '', $image),
            ]);

            if (!empty($item->mechantTaxonomy)) {
                $entry->set('merchants', $item->mechantTaxonomy->slug);
            }

            $entry->save();

            if ($new) {
                $this->created[] = $entry;
            } else {
                $this->updated[] = $entry;
            }
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
            $this->deleted[] = $entry;
        }

        return $entries->count();
    }

    protected function uploadImage(AfilliateItem $item): string
    {
        $directory = 'images/affiliate/' . $this->feedName;
        $file      = $directory . '/' . $item->productId . '.jpg';

        if (File::exists(public_path($file))) {
            return $file;
        }

        if (!File::isDirectory(public_path($directory))) {
            File::makeDirectory(public_path($directory), 0755, true, true);
        }

        File::put(
            public_path($file),
            file_get_contents($item->image)
        );

        return $file;
    }

    protected function merchantTaxonomy(string $merchantName): ?LocalizedTerm
    {
        if (empty($merchantName)) {
            return null;
        }

        $merchant = Taxonomy::find('merchants')
            ->queryTerms()
            ->where('title', '=', $merchantName)
            ->first();

        if (empty($merchant)) {
            Term::make()
                ->taxonomy('merchants')
                ->slug(Str::slug($merchantName))
                ->data(['title' => $merchantName])
                ->save();

            $merchant = Taxonomy::find('merchants')
                ->queryTerms()
                ->where('title', '=', $merchantName)
                ->first();
        }

        return $merchant;
    }

}
