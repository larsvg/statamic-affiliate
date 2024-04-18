<?php

namespace Larsvg\StatamicAffiliate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:make:importer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new importer command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('What is the name of the importer? (Only letters)');
        $name = preg_replace('/[^A-z]/', '', strtolower($name));

        if(empty($name)) {
            $this->error('Invalid name');
            return;
        }

        $content = File::get(__DIR__ . '/../../stubs/commands/DemoAffiliateCommand.php.stub');
        $content = str_replace('{feed-name-ucfirst}', ucfirst($name), $content);
        $content = str_replace('{feed-name}', $name, $content);


    }
}
