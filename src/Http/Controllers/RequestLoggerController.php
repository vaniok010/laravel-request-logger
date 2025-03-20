<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Http\Controllers;

use Hryha\RequestLogger\Data\FiltersData;
use Hryha\RequestLogger\Formatters\Formatter;
use Hryha\RequestLogger\Http\Requests\ListOfLogsFormRequest;
use Hryha\RequestLogger\Stores\Store;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class RequestLoggerController
{
    public function __construct(private Store $store)
    {
    }

    public function index(): ViewContract
    {
        $darkMode = Config::boolean('request-logger.dark_mode');

        return View::make('request-logs::layout', [
            'settings' => [
                'basePath' => '/request-logs',
                'customFields' => Config::array('request-logger.custom_fields'),
                'theme' => $darkMode ? 'dark' : 'light',
            ],
        ]);
    }

    public function list(ListOfLogsFormRequest $request): JsonResponse
    {
        $safeData = $request->safe();

        $filters = new FiltersData(
            orderBy: $safeData->string('orderBy', 'sent')->value(),
            orderDir: $safeData->string('orderDir', 'desc')->value(),
            uri: $safeData->string('uri')->value(),
            excludeUris: $safeData->array('excludeUris'),
            methods: $safeData->array('methods'),
            responseStatus: $safeData->integer('responseStatus'),
            fingerprint: $safeData->string('fingerprint')->value(),
            excludeFingerprints: $safeData->array('excludeFingerprints'),
            sentFrom: $safeData->string('sentFrom')->value(),
            sentTo: $safeData->string('sentTo')->value(),
            durationFrom: $safeData->integer('durationFrom'),
            durationTo: $safeData->integer('durationTo'),
            memoryFrom: $safeData->integer('memoryFrom'),
            memoryTo: $safeData->integer('memoryTo'),
            customFields: $safeData->array('customFields'),
        );

        return Response::json($this->store->list($filters));
    }

    public function one(Formatter $formatter, int $id): JsonResponse
    {
        return Response::json(
            $formatter->prepareLog($this->store->one($id))
        );
    }

    public function delete(): void
    {
        $this->store->deleteAll();
    }
}
