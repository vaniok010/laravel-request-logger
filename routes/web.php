<?php

declare(strict_types=1);

use Hryha\RequestLogger\Http\Controllers\RequestLoggerController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

$middleware = Config::array('request-logger.middleware', []);

Route::prefix('request-logs')->name('request-logs.')->middleware($middleware)->group(function (): void {
    Route::prefix('api')->name('api.')->group(function (): void {
        Route::post('/list', [RequestLoggerController::class, 'list'])->name('list');
        Route::get('/{id}', [RequestLoggerController::class, 'one'])->whereNumber('id')->name('one');
        Route::delete('/', [RequestLoggerController::class, 'delete'])->name('delete');
    });

    Route::get('/{view?}', [RequestLoggerController::class, 'index'])
        ->where('view', '(.*)')
        ->name('index');
});
