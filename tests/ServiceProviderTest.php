<?php

declare(strict_types=1);

namespace Dessop\Ocr\Laravel\Tests;

use Dessop\Ocr\Laravel\DessopOcrServiceProvider;
use Illuminate\Contracts\Foundation\Application;
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

    public function test_client_is_registered_in_the_container(): void
    {
        self::assertTrue($this->app->bound(\Dessop\Ocr\OcrClient::class));
        self::assertInstanceOf(\Dessop\Ocr\OcrClient::class, $this->app->make(\Dessop\Ocr\OcrClient::class));
    }
}
