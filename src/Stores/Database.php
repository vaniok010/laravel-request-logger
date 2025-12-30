<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Stores;

use Hryha\RequestLogger\Data\FiltersData;
use Hryha\RequestLogger\Data\LogData;
use Hryha\RequestLogger\Formatters\Formatter;
use Hryha\RequestLogger\Models\RequestLog;
use Hryha\RequestLogger\Models\RequestLogFingerprint;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Throwable;

class Database implements Store
{
    public function __construct(
        protected Formatter $formatter,
    ) {
    }

    public function list(FiltersData $filtersData): LengthAwarePaginator
    {
        $logsTable = (new RequestLog())->getTable();
        $fingerprintsTable = (new RequestLogFingerprint())->getTable();

        return RequestLog::query()
            ->select([
                "$logsTable.id",
                "$logsTable.fingerprint_id",
                "$logsTable.ip",
                "$logsTable.uri",
                "$logsTable.method",
                "$logsTable.response_status",
                "$logsTable.duration",
                "$logsTable.memory",
                "$logsTable.timezone",
                "$logsTable.sent_at",
                "$fingerprintsTable.fingerprint",
                "$fingerprintsTable.repeats",
            ])
            ->joinFingerprint()
            ->applyFilters($filtersData)
            ->paginate(Config::integer('request-logger.logs_per_page'));
    }

    public function one(int $id): RequestLog
    {
        return RequestLog::query()->findOrFail($id);
    }

    public function create(LogData $logData): void
    {
        try {
            RequestLogFingerprint::query()->upsert(
                ['fingerprint' => $logData->fingerprint, 'repeats' => 1],
                ['fingerprint'],
                ['repeats' => DB::raw('repeats + 1')]
            );

            $fingerprint = RequestLogFingerprint::query()->where('fingerprint', $logData->fingerprint)->firstOrFail();

            $uri = Str::of($logData->request->getRequestUri())
                ->substr(0, 250)
                ->when(
                    fn (Stringable $s) => $s->match('/%$/')->isNotEmpty(),
                    fn (Stringable $s) => $s->substr(0, -1)->append('...')
                )
                ->when(
                    fn (Stringable $s) => $s->match('/%[0-9A-Fa-f]$/')->isNotEmpty(),
                    fn (Stringable $s) => $s->substr(0, -2)->append('...')
                )
                ->toString();

            RequestLog::query()->create([
                'fingerprint_id' => $fingerprint->id,
                'ip' => $logData->request->getClientIp(),
                'host' => Str::limit($logData->request->getHost(), 250),
                'uri' => $uri,
                'method' => $logData->request->getMethod(),
                'headers' => $this->formatter->formatRequestHeaders($logData->request),
                'payload' => $this->formatter->formatRequestContent($logData->request),
                'files' => $this->formatter->formatFiles($logData->request),
                'response_status' => $logData->response->getStatusCode(),
                'response_headers' => $this->formatter->formatResponseHeaders($logData->response),
                'response' => $this->formatter->formatResponseContent($logData->response),
                'custom_fields' => $logData->customFields,
                'duration' => $logData->durationMs,
                'memory' => $logData->memoryUsage,
                'timezone' => $logData->localDatetime->getTimezone()->getName(),
                'sent_at' => $logData->localDatetime->utc()->format('Y-m-d H:i:s.u'),
            ]);

        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
        }
    }

    public function deleteOld(): void
    {
        $lastDate = now()
            ->subDays(Config::integer('request-logger.log_keep_days') - 1)
            ->startOfDay();

        $logsToDelete = RequestLog::query()
            ->whereDate('sent_at', '<', $lastDate)
            ->pluck('fingerprint_id', 'id')
            ->toArray();

        if (empty($logsToDelete)) {
            return;
        }

        $lastId = max(array_keys($logsToDelete));

        RequestLog::query()->where('id', '<=', $lastId)->delete();

        $numberOfRepetitions = array_count_values($logsToDelete);

        $fingerprintRepeats = [];
        foreach ($numberOfRepetitions as $fingerprintId => $repeats) {
            $fingerprintRepeats[$repeats][] = $fingerprintId;
        }

        foreach ($fingerprintRepeats as $repeats => $fingerprintIds) {
            $fingerprintChunks = array_chunk($fingerprintIds, 3000);
            foreach ($fingerprintChunks as $ids) {
                RequestLogFingerprint::query()
                    ->whereIn('id', $ids)
                    ->decrement('repeats', $repeats);
            }
        }

        RequestLogFingerprint::query()
            ->where('repeats', 0)
            ->delete();
    }

    public function deleteAll(): void
    {
        RequestLog::query()->delete();
        RequestLogFingerprint::query()->delete();
    }
}
