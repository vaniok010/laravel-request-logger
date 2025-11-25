<?php


declare(strict_types=1);

namespace Hryha\RequestLogger\Tests;

use Hryha\RequestLogger\Models\RequestLog;
use Hryha\RequestLogger\RequestLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class SamplingTest extends TestCase
{
    public function test_when_sampling_is_disabled_all_logs_are_saved(): void
    {
        Config::set('request-logger.sampling.enabled', false);

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseCount(RequestLog::class, 1);
    }

    public function test_when_sampling_rate_is_100_all_logs_are_saved(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 100);

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseCount(RequestLog::class, 1);
    }

    public function test_when_sampling_rate_is_0_no_logs_are_saved(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 0);

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }

    public function test_slow_requests_are_always_logged_regardless_of_sampling_rate(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 0); // 0% sampling
        Config::set('request-logger.sampling.always_log_slow_requests', 20); // 20ms threshold

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        usleep(21000); // 21 milliseconds

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseCount(RequestLog::class, 1);
    }

    public function test_fast_requests_are_not_logged_when_sampling_rate_is_0(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 0);
        Config::set('request-logger.sampling.always_log_slow_requests', 100); // 100ms threshold

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        usleep(10000); // 10 milliseconds

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }

    public function test_heavy_memory_requests_are_always_logged_regardless_of_sampling_rate(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 0); // 0% sampling
        Config::set('request-logger.sampling.always_log_heavy_memory', 1); // 1MB threshold (always matches)

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseCount(RequestLog::class, 1);
    }

    public function test_different_status_codes_use_different_sampling_rates(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 0);   // 0% for 2xx
        Config::set('request-logger.sampling.rates.4xx', 100); // 100% for 4xx
        Config::set('request-logger.sampling.rates.5xx', 100); // 100% for 5xx

        $requestLogger = $this->app->make(RequestLogger::class);

        // 2xx should not be logged
        $request200 = Request::create(uri: '/success');
        $response200 = new Response(status: 200);
        $requestLogger->save($request200, $response200);

        // 4xx should be logged
        $request404 = Request::create(uri: '/not-found');
        $response404 = new Response(status: 404);
        $requestLogger->save($request404, $response404);

        // 5xx should be logged
        $request500 = Request::create(uri: '/error');
        $response500 = new Response(status: 500);
        $requestLogger->save($request500, $response500);

        $this->assertDatabaseCount(RequestLog::class, 2);
        $this->assertDatabaseHas(RequestLog::class, [
            'response_status' => 404,
        ]);
        $this->assertDatabaseHas(RequestLog::class, [
            'response_status' => 500,
        ]);
    }

    public function test_3xx_status_codes_use_correct_sampling_rate(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.3xx', 0); // 0% for 3xx

        $request = Request::create(uri: '/redirect');
        $response = new Response(status: 301);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }

    public function test_5xx_status_codes_use_correct_sampling_rate(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.5xx', 100); // 100% for 5xx

        $request = Request::create(uri: '/error');
        $response = new Response(status: 503);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseCount(RequestLog::class, 1);
    }

    public function test_ignored_paths_are_never_logged_regardless_of_sampling(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 100); // 100% sampling
        Config::set('request-logger.ignore_paths', ['ignored/*']);

        $request = Request::create(uri: '/ignored/path');
        $response = new Response(status: 200);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }

    public function test_sampling_works_with_percentage_rates(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 50); // 50% sampling

        $requestLogger = $this->app->make(RequestLogger::class);

        // Run 100 times to test probability
        for ($i = 0; $i < 100; $i++) {
            $request = Request::create(uri: "/test-$i");
            $response = new Response(status: 200);
            $requestLogger->save($request, $response);
        }

        $savedCount = RequestLog::count();

        // With 50% rate, we expect roughly 30-70 logs (allowing variance)
        $this->assertGreaterThan(30, $savedCount);
        $this->assertLessThan(70, $savedCount);
    }

    public function test_slow_threshold_0_disables_slow_request_logging(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 0);
        Config::set('request-logger.sampling.always_log_slow_requests', 0); // Disabled

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        usleep(21000); // 21 milliseconds

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }

    public function test_memory_threshold_0_disables_heavy_memory_logging(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates.2xx', 0);
        Config::set('request-logger.sampling.always_log_heavy_memory', 0); // Disabled

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseEmpty(RequestLog::class);
    }

    public function test_default_sampling_rate_is_100_when_not_configured(): void
    {
        Config::set('request-logger.sampling.enabled', true);
        Config::set('request-logger.sampling.rates', []); // No rates configured

        $request = Request::create(uri: '/');
        $response = new Response(status: 200);

        $requestLogger = $this->app->make(RequestLogger::class);
        $requestLogger->save($request, $response);

        $this->assertDatabaseCount(RequestLog::class, 1);
    }
}
