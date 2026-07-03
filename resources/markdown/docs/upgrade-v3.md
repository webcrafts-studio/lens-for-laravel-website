# Upgrade to v3.0.0

Lens v3.0.0 is the current development line. This page documents v3 changes as they are completed; it does not mark the release as final.

## Completed v3 Changes

- selectable WCAG 2.0, 2.1, and 2.2 standards in the dashboard
- `--wcag=2.0`, `--wcag=2.1`, and `--wcag=2.2` in the CLI
- WCAG 2.0 retained as the backward-compatible default
- cumulative axe-core rule selection for WCAG 2.1 and 2.2
- correct A/AA classification for WCAG 2.1 and 2.2 tags
- selected WCAG version stored in history, comparisons, baseline metadata, and PDF reports
- URL-aware history comparisons that keep the same rule and selector on different pages separate
- reusable interactive-state scripts in the CLI through `--states=path`
- core compatibility across PHP 8.2+ and Laravel 10–13
- AI Fix restricted to PHP 8.3+, Laravel 12+, and the optional `laravel/ai` SDK
- clear dashboard messaging when only AI Fix is unavailable

## Upgrade Steps

Update the package:

```bash
composer update webcrafts-studio/lens-for-laravel
```

Run migrations to add WCAG version metadata to existing scan history:

```bash
php artisan migrate
```

Existing history rows are assigned WCAG 2.0, matching the scanner behavior before v3.

## Configuration

If you published the config before v3.0.0, add:

```php
'wcag_version' => env('LENS_FOR_LARAVEL_WCAG_VERSION', '2.0'),
'ai_enabled' => env('LENS_FOR_LARAVEL_AI_ENABLED', true),
```

The dashboard can override the configured WCAG version per scan. The CLI can override it with `--wcag=2.1` or `--wcag=2.2`.

## Optional AI Fix

The core package no longer requires an AI SDK. On PHP 8.3+ and Laravel 12+, install AI Fix separately when needed:

```bash
composer require laravel/ai:^0.3.2 --dev
```

Applications on PHP 8.2 or Laravel 10/11 keep scanning, crawling, history, PDF, preview, source mapping, interactive states, baselines, and CLI support. Only AI Fix is unavailable.

## Baselines

Existing commands still use WCAG 2.0 unless configuration or `--wcag` explicitly selects a newer standard.

Create a fresh reviewed baseline when moving an existing CI workflow from WCAG 2.0 to 2.1 or 2.2, because the newer standard can legitimately produce additional violations.
