<?php

namespace Larsvg\StatamicAffiliate\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Larsvg\StatamicAffiliate\StatamicAffiliateServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Larsvg\\StatamicAffiliate\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicAffiliateServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_statamic-affiliate_table.php.stub';
        $migration->up();
        */
    }
}
