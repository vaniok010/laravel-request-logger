<?php

declare(strict_types=1);

namespace Hryha\RequestLogger;

use Hryha\RequestLogger\Console\Commands\ClearRequestLogs;
use Hryha\RequestLogger\Formatters\Formatter;
use Hryha\RequestLogger\Stores\Store;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class RequestLoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->configure();

        $this->app->singleton(RequestLogger::class);

        $this->app->bind(Store::class, Config::string('request-logger.store'));
        $this->app->bind(Formatter::class, Config::string('request-logger.formatter'));
    }

    public function boot(): void
    {
        $this->registerCommands();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerResources();
        $this->offerPublishing();
    }

    protected function configure(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/request-logger.php',
            'request-logger'
        );
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    protected function registerResources(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'request-logs');
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearRequestLogs::class,
            ]);
        }
    }

    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/request-logger.php' => config_path('request-logger.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/request-logger'),
            ], 'public');
        }
    }
}
