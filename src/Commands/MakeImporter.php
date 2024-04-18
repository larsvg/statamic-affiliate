<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeImporter extends Command
{
    protected $signature   = 'affiliate:make:importer';
    protected $description = 'Create a new importer command';

    public function handle(): int
    {
        $name = $this->ask('What is the name of the feed? (Only letters)');
        $name = preg_replace('/[^A-z]/', '', strtolower($name));

        if (empty($name)) {
            $this->error('Invalid name');
            return self::FAILURE;
        }

        $content  = File::get(__DIR__ . '/../../stubs/commands/DemoAffiliateCommand.php.stub');
        $content  = str_replace('{feed-name-ucfirst}', ucfirst($name), $content);
        $content  = str_replace('{feed-name}', $name, $content);
        $fileName = ucfirst($name) . 'AffiliateCommand.php';
        $target   = app_path('console/Commands/') . $fileName;
        if (File::exists($target)) {
            $this->error('console/Commands/' . $fileName . ' already exists');
            return self::FAILURE;
        }

        File::put($target, $content);

        $this->info('Command created: ' . $fileName . '. Run `php artisan affiliate:' . $name . '-import` to import the feed.');
        $this->info('Add the command to the schedule in app/Console/Kernel.php');

        return self::SUCCESS;
    }

}
