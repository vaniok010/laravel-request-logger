<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Builders;

use Hryha\RequestLogger\Data\FiltersData;
use Hryha\RequestLogger\Models\RequestLog;
use Hryha\RequestLogger\Models\RequestLogFingerprint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @template TModelClass of RequestLog
 *
 * @extends Builder<TModelClass>
 */
class RequestLogBuilder extends Builder
{
    public function __construct(QueryBuilder $query)
    {
        parent::__construct($query);
    }

    public function applyFilters(FiltersData $filtersData): self
    {
        if (!empty($filtersData->uri)) {
            $this->whereLike('uri', "%$filtersData->uri%");
        }

        if (!empty($filtersData->excludeUris)) {
            foreach ($filtersData->excludeUris as $uri) {
                $this->whereNotLike('uri', "%$uri%");
            }
        }

        if (!empty($filtersData->methods)) {
            $this->whereIn('method', $filtersData->methods);
        }

        if ($filtersData->responseStatus > 0) {
            $this->where('response_status', $filtersData->responseStatus);
        }

        if (!empty($filtersData->fingerprint)) {
            $this->where('fingerprint', $filtersData->fingerprint);
        }

        if (!empty($filtersData->excludeFingerprints)) {
            $this->whereNotIn('fingerprint', $filtersData->excludeFingerprints);
        }

        if (!empty($filtersData->customFields)) {
            foreach ($filtersData->customFields as $customField => $value) {
                $this->where("custom_fields->$customField", $value);
            }
        }

        if (!empty($filtersData->sentFrom)) {
            $this->whereDate('sent_at', '>=', $filtersData->sentFrom);
        }

        if (!empty($filtersData->sentTo)) {
            $this->whereDate('sent_at', '<=', $filtersData->sentTo);
        }

        if ($filtersData->durationFrom > 0) {
            $this->where('duration', '>=', $filtersData->durationFrom);
        }

        if ($filtersData->durationTo > 0) {
            $this->where('duration', '<=', $filtersData->durationTo);
        }

        if ($filtersData->memoryFrom > 0) {
            $this->where('memory', '>=', $filtersData->memoryFrom);
        }

        if ($filtersData->memoryTo > 0) {
            $this->where('memory', '<=', $filtersData->memoryTo);
        }

        if ('sent' === $filtersData->orderBy) {
            $this->orderBy('sent_at', $filtersData->orderDir);
        } else {
            $this->orderBy($filtersData->orderBy, $filtersData->orderDir);
        }

        return $this;
    }

    public function joinFingerprint(): self
    {
        $logsTable = (new RequestLog())->getTable();
        $fingerprintsTable = (new RequestLogFingerprint())->getTable();

        return $this
            ->select([
                "$logsTable.*",
                "$fingerprintsTable.fingerprint",
                "$fingerprintsTable.repeats",
            ])
            ->join(
                table: $fingerprintsTable,
                first: "$fingerprintsTable.id",
                operator: '=',
                second: "$logsTable.fingerprint_id"
            );
    }
}
