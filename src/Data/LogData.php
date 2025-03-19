<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Data;

use DateTime;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class LogData
{
    public function __construct(
        public Request $request,
        public Response $response,
        public DateTime $loggerStart,
        public int|float $durationMs,
        public float $memoryUsage,
        public string $fingerprint,
        public array $customFields
    ) {
    }
}
