<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeImporter extends Command
{
    protected $signature = 'affiliate:make:importer';
    protected $description = 'Create a new importer command';

    public function handle(): int
    {
        $name = $this->ask('What is the name of the importer? (Only letters)');
        $name = preg_replace('/[^A-z]/', '', strtolower($name));

        if(empty($name)) {
            $this->error('Invalid name');
            return self::FAILURE;
        }

        $content = File::get(__DIR__ . '/../../stubs/commands/DemoAffiliateCommand.php.stub');
        $content = str_replace('{feed-name-ucfirst}', ucfirst($name), $content);
        $content = str_replace('{feed-name}', $name, $content);

        File::put(app_path('console/Commands/') . ucfirst($name).'AffiliateCommand.php', $content);

        return self::SUCCESS;
    }
}
