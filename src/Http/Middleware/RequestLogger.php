<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Http\Middleware;

use Closure;
use Hryha\RequestLogger\RequestLogger as RequestLoggerService;
use Illuminate\Http\Request;

class RequestLogger
{
    public function __construct(public RequestLoggerService $requestLogger)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        $this->requestLogger->save($request, $response);

        return $response;
    }
}
