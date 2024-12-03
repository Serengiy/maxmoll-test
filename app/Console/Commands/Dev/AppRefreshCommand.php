<?php

namespace App\Console\Commands\Dev;

use Illuminate\Console\Command;

class AppRefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('migrate:refresh');
        $this->call('db:seed');
    }
}
