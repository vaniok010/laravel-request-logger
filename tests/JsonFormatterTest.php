<?php

declare(strict_types=1);

namespace Hryha\RequestLogger\Tests;

use Hryha\RequestLogger\Formatters\JsonFormatter;
use Hryha\RequestLogger\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class JsonFormatterTest extends TestCase
{
    private JsonFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatter = app(JsonFormatter::class);

        Config::set('request-logger.hide.mask', '***');
    }

    public function test_request_headers_can_be_formatted(): void
    {
        $request = Request::create(uri: '/');
        $request->headers->replace([
            'Accept' => 'application/json',
            'Token' => uniqid(),
        ]);

        Config::set('request-logger.hide.request.headers', ['token']);

        $formattedHeaders = $this->formatter->formatRequestHeaders($request);

        $this->assertSame($formattedHeaders, [
            'accept' => 'application/json',
            'token' => '***',
        ]);
    }

    public function test_request_content_can_be_formatted(): void
    {
        $request = Request::create(uri: '/', method: 'POST', content: json_encode([
            'login' => 'username',
            'password' => 'password',
        ]));
        $request->headers->add(['Content-Type' => 'application/json']);

        Config::set('request-logger.hide.request.content', ['password']);

        $formattedContent = $this->formatter->formatRequestContent($request);

        $this->assertSame((array) json_decode($formattedContent), [
            'login' => 'username',
            'password' => '***',
        ]);
    }

    public function test_request_content_can_be_formatted_if_content_type_is_not_json(): void
    {
        $content = '<?xml version="1.0"?><body>request</body>';
        $request = Request::create(uri: '/', method: 'POST', content: $content);

        $formattedContent = $this->formatter->formatRequestContent($request);

        $this->assertEquals((array) json_decode($formattedContent), ['raw' => $content]);
    }

    public function test_request_files_can_be_formatted(): void
    {
        $filename = uniqid('test_file').'.txt';
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;
        file_put_contents($path, 'Test content');

        $file = new UploadedFile($path, $filename, 'text/plain', null, true);

        $request = Request::create(uri: '/', method: 'POST', files: [$file]);
        $request->headers->add(['Content-Type' => 'multipart/form-data']);

        $formattedFiles = $this->formatter->formatFiles($request);

        $this->assertSame($formattedFiles[0], [
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'error' => $file->getError(),
            'originalPath' => $file->getRealPath(),
            'hashName' => $file->hashName(),
            'pathName' => $file->getPath(),
            'fileName' => $file->getFilename(),
        ]);
    }

    public function test_request_files_can_be_formatted_after_delete(): void
    {
        $filename = uniqid('test_file').'.txt';
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;
        file_put_contents($path, 'Test content');

        $file = new UploadedFile($path, $filename, 'text/plain', null, true);

        $request = Request::create(uri: '/', method: 'POST', files: [$file]);
        $request->headers->add(['Content-Type' => 'multipart/form-data']);

        File::delete($file->getRealPath());

        $formattedFiles = $this->formatter->formatFiles($request);

        $this->assertSame($formattedFiles[0], [
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'error' => $file->getError(),
            'originalPath' => $file->getRealPath(),
            'hashName' => null,
            'pathName' => $file->getPath(),
            'fileName' => $file->getFilename(),
        ]);
    }

    public function test_response_headers_can_be_formatted(): void
    {
        $response = new Response(headers: [
            'Content-type' => 'application/json',
            'Token' => uniqid(),
        ]);

        Config::set('request-logger.hide.response.headers', ['token']);

        $formattedHeaders = $this->formatter->formatResponseHeaders($response);

        $this->assertSame(Arr::only($formattedHeaders, ['content-type', 'token']), [
            'content-type' => 'application/json',
            'token' => '***',
        ]);
    }

    public function test_response_content_can_be_formatted(): void
    {
        $response = new Response(content: json_encode([
            'user_id' => 1,
            'token' => uniqid(),
        ]));

        Config::set('request-logger.hide.response.content', ['token']);

        $formattedContent = $this->formatter->formatResponseContent($response);

        $this->assertSame((array) json_decode($formattedContent), [
            'user_id' => 1,
            'token' => '***',
        ]);
    }

    public function test_response_content_can_be_formatted_if_content_type_is_not_json(): void
    {
        $content = '<?xml version="1.0"?><body>response</body>';
        $response = new Response(content: $content);

        $formattedContent = $this->formatter->formatResponseContent($response);

        $this->assertEquals((array) json_decode($formattedContent), ['raw' => $content]);
    }

    public function test_log_can_be_prepared_for_viewing(): void
    {
        $log = RequestLog::factory()->createOne([
            'payload' => json_encode([
                'id' => 1,
            ]),
            'response' => json_encode([
                'name' => 'test',
            ]),
        ]);

        $preparedLog = $this->formatter->prepareLog($log);

        $this->assertSame($preparedLog->payload, ['id' => 1]);
        $this->assertSame($preparedLog->response, ['name' => 'test']);
    }
}
