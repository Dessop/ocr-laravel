<?php

declare(strict_types=1);

namespace Dessop\Ocr\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

final class Ocr extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Dessop\Ocr\OcrClient::class;
    }
}
