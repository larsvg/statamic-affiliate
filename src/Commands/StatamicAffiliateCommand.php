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

    public function handle(): int
    {

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
            $new   = false;
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

            $productDescriptionAi = $this->generateProductDescriptionAi($item, $entry);

            if ($productDescriptionAi > '') {
                $entry->set('product_description_ai', $productDescriptionAi);
                $entry->set('regenerate_ai_description', false);

                $this->warn('AI enhanced description for ' . $item->productName . ' with ' . $productDescriptionAi);
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

    protected function generateProductDescriptionAi(AfilliateItem $item, Entry $entry): ?string
    {
        if (!config('affiliate.enhance_with_ai')) {
            return null;
        }
        if (empty($entry->get('category'))) {
            return null;
        }
        if ($this->aiEnhancedItems > config('affiliate.max_ai_enhanced_items_per_batch')) {
            return null;
        }
        if ($entry->get('regenerate_ai_description') === false) {
            return null;
        }

        $this->aiEnhancedItems++;

        if (empty($entry->get('category'))) {
            $prompt = $this->promptWithoutCategories($item, $entry);
        } else {
            $prompt = $this->promptWithCategories($item, $entry);
        }

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $description = (string) $result?->choices[0]?->message?->content;
        $description = str_replace('```', '', $description);
        $description = str_replace("markdown\n", '', $description);

        if(empty($description)) {
            $this->error('Could not generate AI description for ' . $item->productName);
            return null;
        }

        return $description;
    }

    private function promptWithoutCategories(AfilliateItem $item, Entry $entry): string
    {
        $prompt = config('affiliate.ai_prompt');
        $prompt = str_replace('{productName}', $item->productName, $prompt);
        $prompt = str_replace('{productDescription}', $item->productDescription, $prompt);

        return $prompt;
    }

    private function promptWithCategories(AfilliateItem $item, Entry $entry): string
    {
        $categoryIds  = $entry->get('category');
        $baseUrl      = config('app.url');
        $categoryUrls = collect($categoryIds)->map(function ($id) use ($baseUrl) {
            $categoryEntry = Entry::find($id);

            if (empty($categoryEntry)) {
                return null;
            }

            if ($categoryEntry->published() === false) {
                return null;
            }

            return $baseUrl .  $categoryEntry->url();
        })->filter()->toArray();

        if(empty($categoryUrls)) {
            return $this->promptWithoutCategories($item, $entry);
        }

        $prompt = config('affiliate.ai_prompt_with_categories');
        $prompt = str_replace('{productName}', $item->productName, $prompt);
        $prompt = str_replace('{productDescription}', $item->productDescription, $prompt);
        $prompt = str_replace('{categories}', implode(', ', $categoryUrls), $prompt);

        return $prompt;
    }

}
