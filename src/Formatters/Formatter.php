<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Formatters;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface Formatter
{
    public function formatRequestHeaders(Request $request): array;

    public function formatRequestContent(Request $request): string;

    public function formatResponseHeaders(Response $response): array;

    public function formatResponseContent(Response $response): string;

    public function prepareLog(mixed $log): mixed;
}
