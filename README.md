# DESSOP OCR for Laravel

Official Laravel integration for the DESSOP OCR API.

**Package:** `dessop/ocr-laravel`  
**Core SDK:** `dessop/ocr`  
**API:** `https://ocr.dessop.com`

## Requirements

- PHP 8.2+
- Laravel 10, 11 or 12

## Installation

```bash
composer require dessop/ocr-laravel
```

The service provider is auto-discovered by Laravel.

## Environment

```env
DESSOP_OCR_URL=https://ocr.dessop.com
DESSOP_OCR_API_KEY=dss_your_key
DESSOP_OCR_TIMEOUT=300
```

Optionally publish the configuration:

```bash
php artisan vendor:publish --tag=dessop-ocr-config
```

## Usage

```php
use Dessop\Ocr\Laravel\Facades\Ocr;

$result = Ocr::process(
    storage_path('app/documentos/factura.pdf')
);

$text = $result->text();
```

Dependency injection is also supported:

```php
use Dessop\Ocr\OcrClient;

public function process(OcrClient $ocr)
{
    $result = $ocr->process($path);
}
```

## Architecture

This package is intentionally limited to the DESSOP OCR transport/integration layer. Classification, field extraction, business rules and Document AI belong to separate services.

## License

MIT. Copyright (c) 2026 DESSOP.
