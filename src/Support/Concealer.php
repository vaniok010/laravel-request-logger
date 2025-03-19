<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Support;

use Illuminate\Support\Facades\Config;

class Concealer
{
    public function hide(array $allFields, array $hideFields = []): array
    {
        $result = [];
        foreach ($allFields as $field => $value) {
            if (is_array($value)) {
                $result[$field] = $this->hide($value, $hideFields);
            } else {
                if (! is_int($field) && in_array($field, $hideFields)) {
                    $result[$field] = Config::string('request-logger.hide.mask');
                } else {
                    $result[$field] = $value;
                }
            }
        }

        return $result;
    }
}
