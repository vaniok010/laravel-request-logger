<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Tests;

use Hryha\RequestLogger\Models\RequestLog;
use Hryha\RequestLogger\RequestLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class RequestLoggerTest extends TestCase
{
    public function test_logs_can_be_saved(): void
    {
        $request = Request::create(uri: '/');
        $response = new Response();

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseCount(RequestLog::class, 1);
    }

    public function test_if_logger_is_disabled_logs_would_not_save(): void
    {
        $request = Request::create(uri: '/');
        $response = new Response();

        Config::set('request-logger.enabled', false);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }

    public function test_if_path_is_ignored_logs_would_not_save(): void
    {
        $request = Request::create(uri: '/test');
        $response = new Response();

        Config::set('request-logger.ignore_paths', ['test']);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }

    public function test_if_response_status_is_ignored_logs_would_not_save(): void
    {
        $request = Request::create(uri: '/');
        $response = new Response(status: 301);

        Config::set('request-logger.ignore_response_statuses', [301]);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }
}
