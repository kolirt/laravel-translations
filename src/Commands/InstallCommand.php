<?php

namespace Kolirt\Translations\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install translations package';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('vendor:publish', ['--provider' => 'Kolirt\\Translations\\ServiceProvider']);
    }
}
