<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAffiliateStubs extends Command
{
    protected $signature = 'affiliate:publish-stubs';

    protected $description = 'Publish the affiliate stubs.';

    public function handle(): int
    {
        File::makeDirectory(resource_path('blueprints/collections/categories'), 0755, true, true);
        File::makeDirectory(resource_path('blueprints/collections/products'), 0755, true, true);
        File::makeDirectory(resource_path('blueprints/taxonomies/merchants'), 0755, true, true);
        File::makeDirectory(resource_path('views/categories'), 0755, true, true);
        File::makeDirectory(resource_path('views/products'), 0755, true, true);

        $this->publishFieldsets();
        $this->publishBlueprints();
        $this->publishContent();
        $this->publishViews();

        $this->comment('Stubs published');

        return self::SUCCESS;
    }

    protected function publishBlueprints(): void
    {
        $blueprints = [
            'collections/categories/category.yaml',
            'collections/products/product.yaml',
            'taxonomies/merchants/merchant.yaml',
        ];

        foreach ($blueprints as $blueprint) {
            File::copy(__DIR__.'/../../stubs/blueprints/'.$blueprint, resource_path('blueprints/'.$blueprint));
        }
    }

    protected function publishViews(): void
    {
        $views = [
            'categories/show.antlers.html',
            'products/show.antlers.html',
        ];

        foreach ($views as $view) {
            File::copy(__DIR__.'/../../stubs/views/'.$view, resource_path('views/'.$view));
        }
    }

    protected function publishFieldsets(): void
    {
        $fieldsets = [
            //
        ];

        foreach ($fieldsets as $fieldset) {
            File::copy(__DIR__.'/../../stubs/fieldsets/'.$fieldset, resource_path('fieldsets/'.$fieldset));
        }
    }

    protected function publishContent(): void
    {
        $contents = [
            'collections/categories.yaml',
            'collections/products.yaml',
        ];

        foreach ($contents as $content) {
            $file = 'content/'.$content;
            File::copy(__DIR__.'/../../stubs/content/'.$content, base_path($file));
        }
    }
}
