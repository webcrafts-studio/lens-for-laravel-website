# Version 3 Upgrades

Lens v3.1 is the current development line. This page keeps the v3.0 foundation and the incremental v3.1 additions together so the main documentation does not need a duplicated version tree.

## What's New in v3.1

v3.1 adds a review-focused AI workflow and scan reliability improvements:

- editable AI Fix proposals with line numbers, indentation shortcuts, live diff updates, keyboard apply, and reset-to-AI
- **Fix All A** and **Fix All AA** progressive review queues
- up to three concurrent generations with per-item queued, loading, ready, failed, applied, and rejected states
- navigation between fixes before the entire queue is ready
- independent edits, retry, rejection, and apply controls for every queue item
- outstanding request cancellation when the modal closes or a newer AI workflow supersedes the current one
- cache-busted Chromium navigation and no-cache headers on every re-scan while preserving the original issue URL
- stable dashboard issue identities so actions stay bound to the current scan
- stronger source matching by rendered filename and numeric `:nth-child(...)` position for repeated Blade, React, and Vue elements

v3.1 requires no new migrations or configuration keys. Update the package and clear any published/compiled views if the dashboard UI was customized:

```bash
composer update webcrafts-studio/lens-for-laravel
php artisan optimize:clear
```

If package views were previously published, compare them with the v3.1 dashboard before expecting the new controls to appear.

## v3.0 Foundation

- selectable WCAG 2.0, 2.1, and 2.2 standards in the dashboard
- `--wcag=2.0`, `--wcag=2.1`, and `--wcag=2.2` in the CLI
- WCAG 2.0 retained as the backward-compatible default
- cumulative axe-core rule selection for WCAG 2.1 and 2.2
- correct A/AA classification for WCAG 2.1 and 2.2 tags
- selected WCAG version stored in history, comparisons, baseline metadata, and PDF reports
- URL-aware history comparisons that keep the same rule and selector on different pages separate
- reusable interactive-state scripts in the CLI through `--states=path`
- consistent `ignore_https_errors` behavior across scans, HTTP/browser crawling, and previews
- core compatibility across PHP 8.2+ and Laravel 10–13
- AI Fix restricted to PHP 8.3+, Laravel 12+, and the optional `laravel/ai` SDK
- clear dashboard messaging when only AI Fix is unavailable
- dedicated accessibility-fix agent with a 12000-token ceiling, deterministic temperature, and bounded Gemini thinking
- semantic element/component context and minimal replacements instead of broad line windows
- one controlled retry for token-limit or malformed structured responses, safe user errors, and provider/model/token diagnostics
- immediate pending-verification markers for applied AI fixes, without removing them from axe-derived counts before re-scan
- complete English, Polish, Spanish, French, and German catalogs for package-owned scanner, history, comparison, modal, recorder, PDF, and error text

## Upgrading from v2 to v3.0.0

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

Lens continues to select only Gemini, OpenAI, or Anthropic. It does not pin a model; `laravel/ai` uses the configured provider's default model.

## Baselines

Existing commands still use WCAG 2.0 unless configuration or `--wcag` explicitly selects a newer standard.

Create a fresh reviewed baseline when moving an existing CI workflow from WCAG 2.0 to 2.1 or 2.2, because the newer standard can legitimately produce additional violations.
