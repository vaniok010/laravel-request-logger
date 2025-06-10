<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Formatters;

use Hryha\RequestLogger\Models\RequestLog;
use Hryha\RequestLogger\Support\Concealer;
use Hryha\RequestLogger\Support\JsonEncoder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class JsonFormatter implements Formatter
{
    public function __construct(protected Concealer $concealer)
    {
    }

    public function formatRequestHeaders(Request $request): array
    {
        $headers = $request->headers->all();
        foreach ($headers as $header => $values) {
            $headers[$header] = implode(',', $values);
        }

        return $this->concealer->hide($headers, Config::array('request-logger.hide.request.headers'));
    }

    public function formatRequestContent(Request $request): string
    {
        try {
            $content = $request->getContent();
            if (!empty($content)) {
                $content = JsonEncoder::decode($content);
                $content = $this->concealer->hide($content, Config::array('request-logger.hide.request.content'));
            }
        } catch (Throwable $e) {
            $content = [
                'raw' => $request->getContent(),
            ];
        }

        return is_array($content) ? JsonEncoder::encode($content) : $content;
    }

    public function formatFiles(Request $request): ?array
    {
        if (empty($request->allFiles())) {
            return null;
        }

        return collect($request->allFiles())->map(fn (UploadedFile $file) => [
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'error' => $file->getError(),
            'originalPath' => $file->getRealPath(),
            'hashName' => rescue(fn () => $file->hashName()),
            'pathName' => $file->getPath(),
            'fileName' => $file->getFilename(),
        ])->values()->toArray();
    }

    public function formatResponseContent(Response $response): string
    {
        try {
            $content = $response->getContent();
            if (!empty($content)) {
                $content = JsonEncoder::decode($content);
                $content = $this->concealer->hide($content, Config::array('request-logger.hide.response.content'));
            }
        } catch (Throwable $e) {
            $content = [
                'raw' => $response->getContent(),
            ];
        }

        return is_array($content) ? JsonEncoder::encode($content) : (string)$content;
    }

    public function formatResponseHeaders(Response $response): array
    {
        $headers = $response->headers->all();
        foreach ($headers as $header => $values) {
            $headers[$header] = implode(',', $values);
        }

        return $this->concealer->hide($headers, Config::array('request-logger.hide.response.headers'));
    }

    /** @param RequestLog $log */
    public function prepareLog(mixed $log): mixed
    {
        if (!empty($log->payload)) {
            $log->payload = JsonEncoder::decode($log->payload);
        }

        if (!empty($log->response)) {
            $log->response = JsonEncoder::decode($log->response);
        }

        return $log;
    }
}
