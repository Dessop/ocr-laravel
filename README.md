# DESSOP OCR for Laravel

<p align="center">
  <strong>Official Laravel integration for the DESSOP OCR API.</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/dessop/ocr-laravel">
    <img src="https://img.shields.io/packagist/v/dessop/ocr-laravel.svg?style=flat-square" alt="Packagist Version" />
  </a>
  <a href="https://php.net">
    <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+" />
  </a>
  <a href="https://laravel.com">
    <img src="https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012%20%7C%2013-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 10, 11, 12 or 13" />
  </a>
  <a href="https://github.com/dessop/dessop-ocr-suite/blob/main/LICENSE">
    <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="MIT License" />
  </a>
</p>

<div align="center">

**Package:** `dessop/ocr-laravel`  
**Core SDK:** `dessop/ocr`  
**API:** `https://ocr.dessop.com`

</div>

---

## Overview

This package connects a Laravel application to the DESSOP OCR service so you can process documents, extract text and integrate OCR workflows without handling the lower-level HTTP transport yourself.

## Requirements

- PHP 8.2+
- Laravel 10, 11, 12 or 13

## Installation

```bash
composer require dessop/ocr-laravel
```

The service provider is auto-discovered by Laravel, so no manual registration is needed in most cases.

## Configuration

Add the following values to your `.env` file:

```env
DESSOP_OCR_URL=https://ocr.dessop.com
DESSOP_OCR_API_KEY=dss_your_key
DESSOP_OCR_TIMEOUT=300
```

If you want to publish the package configuration explicitly:

```bash
php artisan vendor:publish --tag=dessop-ocr-config
```

## Usage

### Process a file with the facade

```php
use Dessop\Ocr\Laravel\Facades\Ocr;

$result = Ocr::process(
    storage_path('app/documentos/factura.pdf')
);

$text = $result->text();
```

### Process asynchronously

```php
use Dessop\Ocr\Laravel\Facades\Ocr;

$job = Ocr::processAsync(
    storage_path('app/documentos/factura.pdf')
);

$jobId = $job->jobId();
```

### Check and retry a job

```php
use Dessop\Ocr\Laravel\Facades\Ocr;

$job = Ocr::getJob($jobId);
Ocr::retryJob($jobId);
```

### Health check

```php
use Dessop\Ocr\Laravel\Facades\Ocr;

if (Ocr::health()) {
    // DESSOP OCR is available.
}
```

### Document operations

```php
use Dessop\Ocr\Laravel\Facades\Ocr;

$documents = Ocr::documents();
$document = Ocr::document($documentId);
$text = Ocr::documentText($documentId);
$fileContent = Ocr::documentFile($documentId);
$ocrPdf = Ocr::documentOcrPdf($documentId);
```

### Inject the client directly

```php
use Dessop\Ocr\OcrClient;

public function process(OcrClient $ocr)
{
    $result = $ocr->process($path);

    return $result->text();
}
```

## Why this package exists

This package is intentionally focused on the OCR transport and integration layer.

It keeps the Laravel side clean and lightweight while leaving these concerns outside the package scope:

- Classification
- Field extraction
- Business rules
- Document AI workflows
- Custom validation logic

## Architecture

```text
Laravel app
    └── dessop/ocr-laravel
        └── dessop/ocr SDK
            └── DESSOP OCR API
```

This integration is designed to be the bridge between your application and the OCR service, while domain-specific intelligence remains in separate services.

---

## License

MIT. Copyright (c) 2026 DESSOP.
