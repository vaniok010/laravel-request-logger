<?php

declare(strict_types=1);

namespace Hryha\RequestLogger;

use Hryha\RequestLogger\Data\LogData;
use Hryha\RequestLogger\Stores\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

final class RequestLogger
{
    public LogData $logData;

    private Store $store;

    private Request $request;

    private array $customFields = [];

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function save(Request $request, Response $response): void
    {
        if (!Config::boolean('request-logger.enabled')) {
            return;
        }

        $this->request = $request;
        $duration = $this->getDuration();
        $memory = $this->getMemoryUsage();
        $statusCode = $response->getStatusCode();

        if ($this->shouldIgnorePath($request)) {
            return;
        }

        if (!$this->shouldLog($statusCode, $duration, $memory)) {
            return;
        }

        $this->logData = new LogData(
            request: $request,
            response: $response,
            sentAt: $this->sentAt(),
            durationMs: $duration,
            memoryUsage: $memory,
            fingerprint: $this->getFingerprint(),
            customFields: $this->getCustomFields()
        );

        $this->store->create($this->logData);
    }

    private function getStartTime(): float
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : (float)$this->request->server('REQUEST_TIME_FLOAT');
        if (!mb_strpos("$startTime", '.')) {
            $startTime .= '.0001';
        }

        return (float)$startTime;
    }

    private function sentAt(): Carbon
    {
        return Carbon::createFromTimestamp($this->getStartTime());
    }

    private function getDuration(): float
    {
        $startTime = $this->getStartTime();

        return round((microtime(true) - $startTime) * 1000);
    }

    private function getMemoryUsage(): float
    {
        return round(memory_get_peak_usage(true) / 1024 / 1024, 1);
    }

    public function getFingerprint(): string
    {
        return sha1(implode('|', [
            $this->request->method(),
            $this->request->fullUrl(),
            $this->request->getContent(),
        ]));
    }

    private function shouldIgnorePath(Request $request): bool
    {
        $ignorePaths = Config::array('request-logger.ignore_paths', []);
        $ignorePaths = array_unique(array_merge($ignorePaths, ['request-logs*']));

        foreach ($ignorePaths as $ignorePath) {
            if ($request->is($ignorePath)) {
                return true;
            }
        }

        return false;
    }

    private function shouldLog(int $statusCode, float $durationMs, float $memoryMb): bool
    {
        if (!Config::boolean('request-logger.sampling.enabled')) {
            return true;
        }

        $slowThreshold = Config::integer('request-logger.sampling.always_log_slow_requests');
        if ($slowThreshold > 0 && $durationMs >= $slowThreshold) {
            return true;
        }

        $memoryThreshold = Config::integer('request-logger.sampling.always_log_heavy_memory');
        if ($memoryThreshold > 0 && $memoryMb >= $memoryThreshold) {
            return true;
        }

        $samplingRates = Config::array('request-logger.sampling.rates', []);
        $statusGroup = mb_substr((string)$statusCode, 0, 1).'xx';
        $samplingRate = $samplingRates[$statusGroup] ?? 100.0;

        if ($samplingRate >= 100.0) {
            return true;
        }

        if ($samplingRate <= 0) {
            return false;
        }

        return (mt_rand() / mt_getrandmax()) < ($samplingRate / 100.0);
    }

    private function getCustomFields(): array
    {
        return $this->customFields;
    }

    public function addCustomField(string $key, mixed $value): void
    {
        $this->customFields[$key] = $value;
    }
}
