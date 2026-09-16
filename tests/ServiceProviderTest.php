<?php

declare(strict_types=1);

namespace Dessop\Ocr\Laravel\Tests;

use Dessop\Ocr\Contracts\OcrClientInterface;
use Dessop\Ocr\Laravel\DessopOcrServiceProvider;
use Dessop\Ocr\Laravel\Facades\Ocr;
use Dessop\Ocr\Models\OcrResult;
use Dessop\Ocr\OcrClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Facade;
use Orchestra\Testbench\TestCase;

final class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [DessopOcrServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('dessop-ocr.api_key', 'dss_test_key');
        $app['config']->set('dessop-ocr.url', 'https://ocr.dessop.com');
        $app['config']->set('dessop-ocr.timeout', 30);
    }

    public function test_configuration_is_loaded(): void
    {
        self::assertSame('https://ocr.dessop.com', config('dessop-ocr.url'));
        self::assertSame('dss_test_key', config('dessop-ocr.api_key'));
        self::assertSame(30, config('dessop-ocr.timeout'));
    }

    public function test_client_is_registered_in_the_container_and_bindings_are_available(): void
    {
        self::assertTrue($this->app->bound(OcrClient::class));
        self::assertTrue($this->app->bound(OcrClientInterface::class));
        self::assertInstanceOf(OcrClient::class, $this->app->make(OcrClient::class));
        self::assertInstanceOf(OcrClient::class, $this->app->make(OcrClientInterface::class));
    }

    public function test_process_method_is_available_via_facade(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'ocr_');
        file_put_contents($file, 'hello world');

        $mock = \Mockery::mock(OcrClient::class);
        $mock->shouldReceive('process')->once()->with($file)->andReturn(new OcrResult([
            'success' => true,
            'text' => 'OCR OK',
        ]));

        $this->app->instance(OcrClient::class, $mock);
        Facade::clearResolvedInstance(Ocr::getFacadeAccessor());

        $result = Ocr::process($file);

        self::assertSame('OCR OK', $result->text());
        unlink($file);
    }

    public function test_async_and_job_methods_are_available_via_facade(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'ocr_async_');
        file_put_contents($file, 'async');

        $mock = \Mockery::mock(OcrClient::class);
        $mock->shouldReceive('processAsync')->once()->with($file)->andReturn(new OcrResult([
            'success' => true,
            'job_id' => 321,
            'status' => 'pending',
        ]));
        $mock->shouldReceive('getJob')->once()->with(321)->andReturn(new OcrResult([
            'success' => true,
            'job_id' => 321,
            'status' => 'completed',
            'text' => 'complete',
        ]));
        $mock->shouldReceive('retryJob')->once()->with(321)->andReturn(new OcrResult([
            'success' => true,
            'job_id' => 321,
            'status' => 'processing',
        ]));

        $this->app->instance(OcrClient::class, $mock);
        Facade::clearResolvedInstance(Ocr::getFacadeAccessor());

        $async = Ocr::processAsync($file);
        $job = Ocr::getJob(321);
        $retried = Ocr::retryJob(321);

        self::assertSame('pending', $async->status());
        self::assertSame('complete', $job->text());
        self::assertSame('processing', $retried->status());
        unlink($file);
    }

    public function test_health_and_passthrough_sdk_methods_work(): void
    {
        $mock = \Mockery::mock(OcrClient::class);
        $mock->shouldReceive('health')->once()->andReturnTrue();
        $mock->shouldReceive('documents')->once()->andReturn([
            new OcrResult(['document_id' => 10, 'status' => 'completed']),
        ]);
        $mock->shouldReceive('document')->once()->with(10)->andReturn(new OcrResult(['document_id' => 10, 'text' => 'doc text']));
        $mock->shouldReceive('documentText')->once()->with(10)->andReturn('doc text');
        $mock->shouldReceive('documentFile')->once()->with(10)->andReturn('binary pdf');
        $mock->shouldReceive('documentOcrPdf')->once()->with(10)->andReturn('pdf binary');

        $this->app->instance(OcrClient::class, $mock);
        Facade::clearResolvedInstance(Ocr::getFacadeAccessor());

        self::assertTrue(Ocr::health());
        self::assertCount(1, Ocr::documents());
        self::assertSame('doc text', Ocr::document(10)->text());
        self::assertSame('doc text', Ocr::documentText(10));
        self::assertSame('binary pdf', Ocr::documentFile(10));
        self::assertSame('pdf binary', Ocr::documentOcrPdf(10));
    }

    public function test_sdk_http_client_is_compatible_with_updated_contract(): void
    {
        $http = new Client([
            'handler' => \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([
                new Response(200, [], json_encode(['success' => true, 'text' => 'ok'])),
                new Response(200, [], json_encode(['success' => true, 'job_id' => 99, 'status' => 'pending'])),
                new Response(200, [], json_encode(['success' => true, 'status' => 'completed', 'text' => 'done'])),
                new Response(200, [], json_encode(['success' => true, 'status' => 'processing'])),
                new Response(200),
            ])),
        ]);

        $client = new OcrClient('https://ocr.dessop.com', 'dss_test_key', 30, $http);
        $file = tempnam(sys_get_temp_dir(), 'ocr_sdk_');
        file_put_contents($file, 'sdk');

        self::assertSame('ok', $client->process($file)->text());
        self::assertSame('pending', $client->processAsync($file)->status());
        self::assertSame('done', $client->getJob(99)->text());
        self::assertSame('processing', $client->retryJob(99)->status());
        self::assertTrue($client->health());

        unlink($file);
    }
}
