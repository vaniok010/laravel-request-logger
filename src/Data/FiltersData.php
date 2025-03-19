<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Data;

final class FiltersData
{
    public function __construct(
        public string $orderBy = 'sent',
        public string $orderDir = 'desc',
        public ?string $uri = null,
        public ?array $excludeUris = null,
        public ?array $methods = null,
        public ?int $responseStatus = null,
        public ?string $fingerprint = null,
        public ?array $excludeFingerprints = null,
        public ?string $sentFrom = null,
        public ?string $sentTo = null,
        public ?int $durationFrom = null,
        public ?int $durationTo = null,
        public ?int $memoryFrom = null,
        public ?int $memoryTo = null,
        public ?array $customFields = null,
    ) {
    }
}
