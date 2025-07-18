<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'request-logs:install';

    /**
     * @var string
     */
    protected $description = 'Install all of the Request Logger resources';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->comment('Publishing Request Logger Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'request-logger-assets']);

        $this->comment('Publishing Request Logger Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'request-logger-config']);

        $this->info('Request Logger scaffolding installed successfully.');
    }
}
