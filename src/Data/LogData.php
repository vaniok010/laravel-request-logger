<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Data;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

final class LogData
{
    public function __construct(
        public Request $request,
        public Response $response,
        public Carbon $localDatetime,
        public int|float $durationMs,
        public float $memoryUsage,
        public string $fingerprint,
        public array $customFields
    ) {
    }
}
