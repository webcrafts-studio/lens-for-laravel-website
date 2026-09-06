# Version 3 Upgrades

Lens v3.5 is the current development line. This page keeps the v3.0 foundation and the incremental v3.1/v3.2/v3.3/v3.4/v3.5 additions together so the main documentation does not need a duplicated version tree.

## What's New in v3.5

v3.5 adds an optional explicit AI model override plus a dashboard note about the active AI Fix model:

- set `LENS_FOR_LARAVEL_AI_MODEL` to pin one explicit model for any provider at your own responsibility (for example `gpt-5.6-luna` for OpenAI)
- leave it empty to keep the implicit SDK-default behavior; no migration is required
- for Ollama the override takes precedence and `LENS_FOR_LARAVEL_AI_OLLAMA_MODEL` stays as a fallback
- the dashboard scanner card reports whether AI Fix uses the default AI SDK model or a manually configured model name; the note stays hidden while AI Fix is unavailable
- the accessibility agent no longer pins temperature to `0`, so models without temperature support (for example `gpt-5.6-luna`) stop failing with a provider error; generation uses the provider default temperature

If the Lens config was published before v3.5, add:

```php
'ai_model' => env('LENS_FOR_LARAVEL_AI_MODEL'),
```

## What's New in v3.4

v3.4 adds authenticated scans for pages behind login plus four additional AI Fix providers (OpenRouter, xAI, DeepSeek, Mistral):

- enable authenticated scans with `LENS_FOR_LARAVEL_AUTH_ENABLED=true` (default `false`, no migration required)
- scan as an existing user with `php artisan lens:audit <url> --as-user=1` or the dashboard user ID field
- crawling, interactive state scans, and element previews accept the same server-resolved user
- only the numeric user id travels from the client; login happens server-side and session cookies are never stored
- optionally restrict impersonation with `LENS_FOR_LARAVEL_AUTH_ALLOWED_IDS=1,2`
- requires a persistent session driver (`file`, `database`, or `redis`)
- `openrouter`, `xai`, `deepseek`, and `mistral` are accepted by `LENS_FOR_LARAVEL_AI_PROVIDER`
- the new providers keep the implicit SDK-default model behavior of Gemini, OpenAI, and Anthropic; only Ollama uses an explicit model tag
- configure credentials with `OPENROUTER_API_KEY`, `XAI_API_KEY`, `DEEPSEEK_API_KEY`, or `MISTRAL_API_KEY`

If the Lens config was published before v3.4, add:

```php
'auth_enabled' => env('LENS_FOR_LARAVEL_AUTH_ENABLED', false),
'auth_guard' => env('LENS_FOR_LARAVEL_AUTH_GUARD', 'web'),
'auth_allowed_user_ids' => /* comma-separated LENS_FOR_LARAVEL_AUTH_ALLOWED_IDS, e.g. '1,2' */ [],
```

## What's New in v3.3

The only new feature in v3.3 is local AI Fix model support through Ollama:

- set `LENS_FOR_LARAVEL_AI_PROVIDER=ollama`
- select an installed local tag with `LENS_FOR_LARAVEL_AI_OLLAMA_MODEL`
- allow slower local inference with a dedicated 120-second default timeout
- keep cloud providers on their existing SDK-default model behavior
- retain the same semantic context limit, structured output, one controlled retry, review workflow, apply safeguards, logging, and pending re-scan state

No migration is required. If the Lens config was published before v3.3, add:

```php
'ai_ollama_model' => env('LENS_FOR_LARAVEL_AI_OLLAMA_MODEL'),
'ai_ollama_timeout' => env('LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT', 120),
```

## What's New in v3.2

v3.2 improves source mapping without changing the database schema, configuration, or compatibility matrix:

- same-host rendered URLs can resolve to named Laravel routes and match Blade/Livewire helpers such as `route('home')`
- the failing opening tag is parsed separately so nested child attributes and classes are not assigned to the parent
- bounded multiline element blocks and descendant signatures map nested Blade, React, and Vue markup
- nearby ancestor selector context distinguishes similar dynamic elements
- React/Vue structural matches retain priority over weaker Blade fallbacks in mixed applications

No new migration or configuration key is required.

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
- core compatibility across PHP 8.2+ and Laravel 10-13
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
composer require laravel/ai --dev
```

Applications on PHP 8.2 or Laravel 10/11 keep scanning, crawling, history, PDF, preview, source mapping, interactive states, baselines, and CLI support. Only AI Fix is unavailable.

Lens now selects Ollama in addition to Gemini, OpenAI, and Anthropic. The cloud providers continue to use their configured `laravel/ai` default model. Ollama uses `LENS_FOR_LARAVEL_AI_OLLAMA_MODEL` when set, or the SDK's Ollama default when omitted.

## Baselines

Existing commands still use WCAG 2.0 unless configuration or `--wcag` explicitly selects a newer standard.

Create a fresh reviewed baseline when moving an existing CI workflow from WCAG 2.0 to 2.1 or 2.2, because the newer standard can legitimately produce additional violations.
