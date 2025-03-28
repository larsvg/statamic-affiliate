<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Larsvg\StatamicAffiliate\Collections\AffiliateCollection;
use Larsvg\StatamicAffiliate\Collections\AfilliateItem;
use Larsvg\StatamicAffiliate\Events\FeedImported;
use OpenAI\Laravel\Facades\OpenAI;
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

    private int $aiEnhancedItems = 0;

    private array $availableCategories = [];

    public function handle(): int
    {
        $this->availableCategories = Entry::query()
            ->where('collection', 'categories')
            ->get()
            ->map(function($entry) {
                ;
                return [
                    'id'    => $entry->id(),
                    'title' => $entry->title,
                ];
            })
            ->toArray();

        $this->feedName = $this->setFeedName();
        $this->comment('Importing ' . $this->feedName . ' affiliate data');

        $this->affiliateCollection = $this->CollectAffiliateItems();
        $this->importFeed();
        $this->cleanupItemsNotInFeed();

        $this->comment(count($this->updated) . ' updated, ' . count($this->deleted) . ' deleted, ' . count($this->created) . ' created');

        event(new FeedImported($this->feedName, $this->created, $this->updated, $this->deleted));

        return self::SUCCESS;
    }

    protected function importFeed(): void
    {
        foreach ($this->affiliateCollection as $item) {
            $new = false;
            try {
                $entry = Entry::query()
                    ->where('collection', 'products')
                    ->where('product_id', $item->productId)
                    ->where('merchant_id', $item->merchantId)
                    ->first();

            } catch (\Exception $e) {
                $this->error('Error on ' . $item->productName . ': ' . $e->getMessage());
                continue;
            }
            if (empty($entry)) {
                $entry = new Entry();
                $entry->collection('products');
                $entry->set('product_id', $item->productId);
                $entry->set('merchant_id', $item->merchantId);

                $new = true;
            }

            $image = $this->uploadImage($item);

            if (empty($image)) {
                $this->info('Error on image for ' . $item->productName);
                continue;
            }

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
            $entry->set('responsive', [
                'src' => str_replace('images/', '', $image),
            ]);

            $aiEnhancements = $this->enhancePropertiesWithAi($item, $entry);

            if (!empty($aiEnhancements)) {
                if (isset($aiEnhancements['productDescription'])) {
                    $entry->set('product_description_ai', $aiEnhancements['productDescription']);
                    unset($aiEnhancements['productDescription']);
                }

                foreach ($aiEnhancements as $field => $value) {
                    $entry->set($field, $value);
                }

                $this->info('AI enhance: ' . $item->productName);

                $entry->set('regenerate_ai', false);
            }

            if (!empty($item->merchantName)) {
                $entry->set('merchant_name', $item->merchantName);
            }

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

    protected function uploadImage(AfilliateItem $item): ?string
    {
        try {
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
        } catch (\Exception $e) {
            return null;
        }
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

    protected function enhancePropertiesWithAi(AfilliateItem $item, Entry $entry): ?array
    {
        if (!config('affiliate.enhance_with_ai')) {
            $this->info('AI enhancement disabled');
            return null;
        }
        if ($this->aiEnhancedItems > config('affiliate.max_ai_enhanced_items_per_batch')) {
            $this->info('Max AI enhanced items reached');
            return null;
        }
        if ($entry->get('regenerate_ai') === false) {
            $this->info('AI enhancement already done');
            return null;
        }
        $this->aiEnhancedItems++;

        $input  = [
            'productName'         => $item->productName,
            'productDescription'  => $item->productDescription,
            'availableCategories' => $this->availableCategories,
        ];
        $output = config('affiliate.ai_output');
        $prompt = config('affiliate.ai_prompt');
        $prompt = str_replace('{input}', json_encode($input), $prompt);
        $prompt = str_replace('{output}', json_encode($output), $prompt);
        $result = OpenAI::chat()->create([
            'model'    => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        try {
            $json = trim($result?->choices[0]?->message?->content);
            preg_match('/\{.*\}/s', $json, $matches);
            return json_decode($matches[0], true);
        } catch (\Exception $e) {
            return null;
        }
    }

}
