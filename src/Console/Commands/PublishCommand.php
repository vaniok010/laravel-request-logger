<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Console\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'request-logs:publish {--force : Overwrite any existing files}';

    /**
     * @var string
     */
    protected $description = 'Publish all of the Request Logger resources';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'request-logger-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'request-logger-assets',
            '--force' => true,
        ]);
    }
}
