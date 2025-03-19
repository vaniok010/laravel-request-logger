<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Tests;

use Hryha\RequestLogger\Console\Commands\ClearRequestLogs;
use Hryha\RequestLogger\Models\RequestLog;
use Hryha\RequestLogger\Models\RequestLogFingerprint;
use Illuminate\Support\Facades\Config;

class ClearCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_all_logs_can_be_deleted(): void
    {
        RequestLog::factory()->count(5)->create();

        $this->artisan(ClearRequestLogs::class, ['--all' => true]);

        $this->assertDatabaseCount(RequestLog::class, 0);
        $this->assertDatabaseCount(RequestLogFingerprint::class, 0);
    }

    public function test_old_logs_can_be_deleted(): void
    {
        $firstFingerprint = RequestLogFingerprint::factory()->createOne(['repeats' => 2]);
        $secondFingerprint = RequestLogFingerprint::factory()->createOne(['repeats' => 1]);

        // old logs
        RequestLog::factory()->for($firstFingerprint, 'fingerprint')->createOne([
            'sent_at' => now()->subDays(2),
        ]);
        RequestLog::factory()->for($secondFingerprint, 'fingerprint')->createOne([
            'sent_at' => now()->subDay(),
        ]);

        // new log
        $newLog = RequestLog::factory()->for($firstFingerprint, 'fingerprint')->createOne([
            'sent_at' => now(),
        ]);

        Config::set('request-logger.log_keep_days', 1);

        $this->artisan(ClearRequestLogs::class, ['--all' => false]);

        $this->assertDatabaseCount(RequestLog::class, 1);
        $this->assertDatabaseCount(RequestLogFingerprint::class, 1);
        $this->assertDatabaseHas(RequestLog::class, [
            'id' => $newLog->id,
        ]);
        $this->assertDatabaseHas(RequestLogFingerprint::class, [
            'id' => $firstFingerprint->id,
            'repeats' => 1,
        ]);
    }
}
