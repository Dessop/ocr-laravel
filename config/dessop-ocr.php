<?php

declare(strict_types=1);

return [
    'url' => env('DESSOP_OCR_URL', 'https://ocr.dessop.com'),
    'api_key' => env('DESSOP_OCR_API_KEY'),
    'timeout' => (int) env('DESSOP_OCR_TIMEOUT', 300),
];
