<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Tests;

use Hryha\RequestLogger\RequestLoggerServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Hryha\\RequestLogger\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function defineEnvironment($app): void
    {
        tap($app['config'], function (Repository $config): void {
            $config->set('request-logger.table_name', 'request_logs');
        });
    }

    protected function getPackageMigrations($app): array
    {
        return [
            __DIR__.'/../database/migrations',
        ];
    }

    protected function getPackageConfigPath($app): array
    {
        return [
            __DIR__.'/../config/request-logger.php' => 'request-logger',
        ];
    }

    protected function getPackageProviders($app): array
    {
        return [RequestLoggerServiceProvider::class];
    }
}
