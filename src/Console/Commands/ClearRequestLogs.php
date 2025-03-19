<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Console\Commands;

use Hryha\RequestLogger\Stores\Store;
use Illuminate\Console\Command;

class ClearRequestLogs extends Command
{
    /**
     * @var string
     */
    protected $signature = 'request-logs:clear {--all}';

    /**
     * @var string
     */
    protected $description = 'Clear request logs';

    public function handle(Store $store): void
    {
        if ($this->option('all')) {
            $store->deleteAll();
        }

        $store->deleteOld();
    }
}
