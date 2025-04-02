<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Tests;

use Hryha\RequestLogger\Models\RequestLog;
use Hryha\RequestLogger\Models\RequestLogFingerprint;

class ApiTest extends TestCase
{
    public function test_one_log_can_be_returned(): void
    {
        $log = RequestLog::factory()->createOne();

        $this->getJson(route('request-logs.api.one', $log->id))
            ->assertOk()
            ->assertJsonPath('id', $log->id);
    }

    public function test_list_of_logs_can_be_returned(): void
    {
        $log = RequestLog::factory()->createOne();

        $this->postJson(route('request-logs.api.list'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_list_can_be_sorted_by_oldest_date(): void
    {
        $fistLog = RequestLog::factory()->createOne(['sent_at' => now()->subDays(3)]);
        $secondLog = RequestLog::factory()->createOne(['sent_at' => now()->subDays(2)]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'sent', 'orderDir' => 'asc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $fistLog->id)
            ->assertJsonPath('data.1.id', $secondLog->id);
    }

    public function test_list_can_be_sorted_by_newest_date(): void
    {
        $fistLog = RequestLog::factory()->createOne(['sent_at' => now()->subDays(3)]);
        $secondLog = RequestLog::factory()->createOne(['sent_at' => now()->subDays(2)]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'sent', 'orderDir' => 'desc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $secondLog->id)
            ->assertJsonPath('data.1.id', $fistLog->id);
    }

    public function test_list_can_be_sorted_by_low_to_high_status(): void
    {
        $fistLog = RequestLog::factory()->createOne(['response_status' => 200]);
        $secondLog = RequestLog::factory()->createOne(['response_status' => 300]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'response_status', 'orderDir' => 'asc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $fistLog->id)
            ->assertJsonPath('data.1.id', $secondLog->id);
    }

    public function test_list_can_be_sorted_by_high_to_low_status(): void
    {
        $fistLog = RequestLog::factory()->createOne(['response_status' => 200]);
        $secondLog = RequestLog::factory()->createOne(['response_status' => 300]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'response_status', 'orderDir' => 'desc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $secondLog->id)
            ->assertJsonPath('data.1.id', $fistLog->id);
    }

    public function test_list_can_be_sorted_by_shortest_duration(): void
    {
        $fistLog = RequestLog::factory()->createOne(['duration' => 50]);
        $secondLog = RequestLog::factory()->createOne(['duration' => 100]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'duration', 'orderDir' => 'asc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $fistLog->id)
            ->assertJsonPath('data.1.id', $secondLog->id);
    }

    public function test_list_can_be_sorted_by_longest_duration(): void
    {
        $fistLog = RequestLog::factory()->createOne(['duration' => 50]);
        $secondLog = RequestLog::factory()->createOne(['duration' => 100]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'duration', 'orderDir' => 'desc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $secondLog->id)
            ->assertJsonPath('data.1.id', $fistLog->id);
    }

    public function test_list_can_be_sorted_by_low_to_high_memory(): void
    {
        $fistLog = RequestLog::factory()->createOne(['memory' => 5]);
        $secondLog = RequestLog::factory()->createOne(['memory' => 12]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'memory', 'orderDir' => 'asc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $fistLog->id)
            ->assertJsonPath('data.1.id', $secondLog->id);
    }

    public function test_list_can_be_sorted_by_high_to_low_memory(): void
    {
        $fistLog = RequestLog::factory()->createOne(['memory' => 5]);
        $secondLog = RequestLog::factory()->createOne(['memory' => 12]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'memory', 'orderDir' => 'desc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $secondLog->id)
            ->assertJsonPath('data.1.id', $fistLog->id);
    }

    public function test_list_can_be_sorted_by_low_to_high_repeats(): void
    {
        $firstFingerprint = RequestLogFingerprint::factory()->createOne(['repeats' => 5]);
        $fistLog = RequestLog::factory()->createOne([
            'fingerprint_id' => $firstFingerprint->id,
        ]);
        $secondFingerprint = RequestLogFingerprint::factory()->createOne(['repeats' => 10]);
        $secondLog = RequestLog::factory()->createOne([
            'fingerprint_id' => $secondFingerprint->id,
        ]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'repeats', 'orderDir' => 'asc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $fistLog->id)
            ->assertJsonPath('data.1.id', $secondLog->id);
    }

    public function test_list_can_be_sorted_by_high_to_low_repeats(): void
    {
        $firstFingerprint = RequestLogFingerprint::factory()->createOne(['repeats' => 5]);
        $fistLog = RequestLog::factory()->createOne([
            'fingerprint_id' => $firstFingerprint->id,
        ]);
        $secondFingerprint = RequestLogFingerprint::factory()->createOne(['repeats' => 10]);
        $secondLog = RequestLog::factory()->createOne([
            'fingerprint_id' => $secondFingerprint->id,
        ]);

        $this->postJson(route('request-logs.api.list'), ['orderBy' => 'repeats', 'orderDir' => 'desc'])
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $secondLog->id)
            ->assertJsonPath('data.1.id', $fistLog->id);
    }

    public function test_logs_can_be_filtered_by_uri(): void
    {
        RequestLog::factory()->createOne();
        $log = RequestLog::factory()->createOne();

        $this->postJson(route('request-logs.api.list'), ['uri' => $log->uri])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_excluded_by_uris(): void
    {
        $log = RequestLog::factory()->createOne();
        $excludedLog = RequestLog::factory()->createOne();

        $this->postJson(route('request-logs.api.list'), ['excludeUris' => [$excludedLog->uri]])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_methods(): void
    {
        RequestLog::factory()->createOne(['method' => 'GET']);
        $log = RequestLog::factory()->createOne(['method' => 'POST']);

        $this->postJson(route('request-logs.api.list'), ['methods' => [$log->method]])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_response_status(): void
    {
        RequestLog::factory()->createOne(['response_status' => 200]);
        $log = RequestLog::factory()->createOne(['response_status' => 404]);

        $this->postJson(route('request-logs.api.list'), ['responseStatus' => $log->response_status])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_fingerprint(): void
    {
        RequestLog::factory()->createOne();
        $log = RequestLog::factory()->createOne();

        $this->postJson(route('request-logs.api.list'), ['fingerprint' => $log->fingerprint->fingerprint])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_excluded_by_fingerprints(): void
    {
        $log = RequestLog::factory()->createOne();
        $excludedLog = RequestLog::factory()->createOne();

        $this->postJson(route('request-logs.api.list'), ['excludeFingerprints' => [$excludedLog->fingerprint->fingerprint]])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_sent_from(): void
    {
        RequestLog::factory()->createOne(['sent_at' => now()->subDays(3)]);
        $log = RequestLog::factory()->createOne(['sent_at' => now()]);

        $this->postJson(route('request-logs.api.list'), ['sentFrom' => now()->subDay()->toDateTimeString()])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_sent_to(): void
    {
        RequestLog::factory()->createOne(['sent_at' => now()]);
        $log = RequestLog::factory()->createOne(['sent_at' => now()->subDays(2)]);

        $this->postJson(route('request-logs.api.list'), ['sentTo' => now()->subDay()->toDateTimeString()])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_sent_range(): void
    {
        RequestLog::factory()->createOne(['sent_at' => now()->subDays(5)]);
        RequestLog::factory()->createOne(['sent_at' => now()]);
        $log = RequestLog::factory()->createOne(['sent_at' => now()->subDays(2)]);

        $this->postJson(route('request-logs.api.list'), [
            'sentFrom' => now()->subDays(3)->toDateTimeString(),
            'sentTo' => now()->subDay()->toDateTimeString(),
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_duration_from(): void
    {
        RequestLog::factory()->createOne(['duration' => 10]);
        $log = RequestLog::factory()->createOne(['duration' => 20]);

        $this->postJson(route('request-logs.api.list'), ['durationFrom' => 11])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_duration_to(): void
    {
        RequestLog::factory()->createOne(['duration' => 20]);
        $log = RequestLog::factory()->createOne(['duration' => 10]);

        $this->postJson(route('request-logs.api.list'), ['durationTo' => 19])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_duration_range(): void
    {
        RequestLog::factory()->createOne(['duration' => 20]);
        $log = RequestLog::factory()->createOne(['duration' => 10]);

        $this->postJson(route('request-logs.api.list'), ['durationFrom' => 10, 'durationTo' => 19])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_memory_from(): void
    {
        RequestLog::factory()->createOne(['memory' => 10]);
        $log = RequestLog::factory()->createOne(['memory' => 20]);

        $this->postJson(route('request-logs.api.list'), ['memoryFrom' => 11])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_memory_to(): void
    {
        RequestLog::factory()->createOne(['memory' => 20]);
        $log = RequestLog::factory()->createOne(['memory' => 10]);

        $this->postJson(route('request-logs.api.list'), ['memoryTo' => 19])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_memory_range(): void
    {
        RequestLog::factory()->createOne(['memory' => 20]);
        $log = RequestLog::factory()->createOne(['memory' => 10]);

        $this->postJson(route('request-logs.api.list'), ['memoryFrom' => 10, 'memoryTo' => 19])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_filtered_by_custom_fields(): void
    {
        RequestLog::factory()->createOne();
        $log = RequestLog::factory()->createOne(['custom_fields' => ['test' => 'value']]);

        $this->postJson(route('request-logs.api.list'), ['customFields' => ['test' => 'value']])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $log->id);
    }

    public function test_logs_can_be_deleted(): void
    {
        RequestLog::factory()->count(5)->create();

        $this->deleteJson(route('request-logs.api.delete'))
            ->assertOk();

        $this->assertDatabaseEmpty(RequestLog::class);
    }
}
