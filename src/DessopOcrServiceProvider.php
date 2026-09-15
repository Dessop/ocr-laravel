<?php

declare(strict_types=1);

namespace Dessop\Ocr\Laravel;

use Dessop\Ocr\Contracts\OcrClientInterface;
use Dessop\Ocr\OcrClient;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class DessopOcrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/dessop-ocr.php',
            'dessop-ocr'
        );

        $this->app->singleton(OcrClient::class, function () {
            $apiKey = config('dessop-ocr.api_key');

            if (!is_string($apiKey) || $apiKey === '') {
                throw new RuntimeException('DESSOP OCR API Key no está configurada.');
            }

            return new OcrClient(
                (string) config('dessop-ocr.url', 'https://ocr.dessop.com'),
                $apiKey,
                (int) config('dessop-ocr.timeout', 300),
            );
        });

        $this->app->alias(OcrClient::class, OcrClientInterface::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/dessop-ocr.php' => config_path('dessop-ocr.php'),
        ], 'dessop-ocr-config');
    }
}
