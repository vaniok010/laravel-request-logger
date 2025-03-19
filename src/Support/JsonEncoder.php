<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Support;

use RuntimeException;

final class JsonEncoder
{
    public static function decode(string $json): array
    {
        $decoded = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('Failed to decode JSON: '.json_last_error_msg());
        }
        if (!is_array($decoded)) {
            throw new RuntimeException('Decoded JSON is not an array');
        }

        return $decoded;
    }

    public static function encode(array $value): string
    {
        $encoded = json_encode($value);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('Failed to encode JSON: '.json_last_error_msg());
        }
        if (!is_string($encoded)) {
            throw new RuntimeException('Encoded JSON is not a string');
        }

        return $encoded;
    }
}
