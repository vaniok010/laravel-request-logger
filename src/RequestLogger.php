<?php

declare(strict_types=1);

namespace Hryha\RequestLogger;

use DateTime;
use DateTimeZone;
use Hryha\RequestLogger\Data\LogData;
use Hryha\RequestLogger\Stores\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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
        if (!Config::boolean('request-logger.enabled') || $this->shouldIgnore($request, $response)) {
            return;
        }

        $this->request = $request;

        $this->logData = new LogData(
            request: $request,
            response: $response,
            loggerStart: $this->getLoggerStart(),
            durationMs: $this->getDuration(),
            memoryUsage: $this->getMemoryUsage(),
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

    /**
     * @throws RuntimeException
     */
    private function getLoggerStart(): DateTime
    {
        $startTime = $this->getStartTime();
        $loggerStart = DateTime::createFromFormat('U.u', (string)$startTime);
        if (!$loggerStart instanceof DateTime) {
            throw new RuntimeException('Logger start must be a DateTime object');
        }

        try {
            $timezone = new DateTimeZone(Config::string('request-logger.timezone'));
            $loggerStart->setTimezone($timezone);
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
        }

        return $loggerStart;
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

    private function shouldIgnore(Request $request, Response $response): bool
    {
        if ($this->shouldIgnorePath($request)) {
            return true;
        }

        return $this->shouldIgnoreResponseStatus($response);
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

    private function shouldIgnoreResponseStatus(Response $response): bool
    {
        $ignoreStatuses = Config::array('request-logger.ignore_response_statuses', []);
        foreach ($ignoreStatuses as $ignoreStatus) {
            if ($response->getStatusCode() === $ignoreStatus) {
                return true;
            }
            if (is_array($ignoreStatus) && 2 === count($ignoreStatus)) {
                if ($response->getStatusCode() >= $ignoreStatus[0] && $response->getStatusCode() <= $ignoreStatus[1]) {
                    return true;
                }
            }
        }

        return false;
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
