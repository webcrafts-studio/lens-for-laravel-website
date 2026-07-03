# Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="lens-for-laravel-config"
```

This creates:

```text
config/lens-for-laravel.php
```

## Full Config

```php
<?php

return [
    'route_prefix' => 'lens-for-laravel',

    'middleware' => ['web'],

    'enabled_environments' => [
        'local',
    ],

    'locale' => env('LENS_FOR_LARAVEL_LOCALE', app()->getLocale()),

    'fallback_locale' => env('LENS_FOR_LARAVEL_FALLBACK_LOCALE', 'en'),

    'supported_locales' => [
        'en' => 'English',
        'pl' => 'Polski',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',
    ],

    'editor' => env('LENS_FOR_LARAVEL_EDITOR', 'vscode'),

    'crawl_max_pages' => env('LENS_FOR_LARAVEL_CRAWL_MAX_PAGES', 50),

    'crawler_render_javascript' => env('LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT', false),

    'scan_wait_ms' => env('LENS_FOR_LARAVEL_SCAN_WAIT_MS', 0),

    'wcag_version' => env('LENS_FOR_LARAVEL_WCAG_VERSION', '2.0'),

    'baseline_path' => env('LENS_FOR_LARAVEL_BASELINE_PATH', storage_path('app/lens-for-laravel/baseline.json')),

    'ignore_https_errors' => env('LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS', false),

    'ai_enabled' => env('LENS_FOR_LARAVEL_AI_ENABLED', true),

    'ai_provider' => env('LENS_FOR_LARAVEL_AI_PROVIDER', 'gemini'),
];
```

## Options

### `wcag_version`

**Type:** `string` | **Default:** `2.0` | **Env:** `LENS_FOR_LARAVEL_WCAG_VERSION`

Controls the initial WCAG standard in the dashboard and the CLI default when `--wcag` is omitted.

```text
LENS_FOR_LARAVEL_WCAG_VERSION=2.0
```

Supported values are `2.0`, `2.1`, and `2.2`. The dashboard can override this per scan, and the CLI can override it with `--wcag=2.2`. WCAG 2.0 remains the default for backward compatibility.

### `route_prefix`

**Type:** `string` | **Default:** `lens-for-laravel`

Controls the route prefix for the dashboard and API endpoints.

```php
'route_prefix' => 'a11y',
```

Dashboard URL:

```text
/a11y/dashboard
```

### `middleware`

**Type:** `array` | **Default:** `['web']`

Middleware applied to all Lens routes.

```php
'middleware' => ['web', 'auth'],
```

Use authenticated middleware if Lens is enabled outside local development.

### `enabled_environments`

**Type:** `array` | **Default:** `['local']`

Lens returns `403 Forbidden` outside these environments.

```php
'enabled_environments' => ['local', 'testing'],
```

Do not enable Lens publicly in production.

### `locale`

**Type:** `string` | **Default:** `app()->getLocale()` | **Env:** `LENS_FOR_LARAVEL_LOCALE`

Controls the default dashboard language.

```text
LENS_FOR_LARAVEL_LOCALE=pl
```

Users can change the language in the dashboard. Their selection is stored in the session.

### `fallback_locale`

**Type:** `string` | **Default:** `en` | **Env:** `LENS_FOR_LARAVEL_FALLBACK_LOCALE`

Fallback language used when a translation key is missing in the active locale.

```text
LENS_FOR_LARAVEL_FALLBACK_LOCALE=en
```

### `supported_locales`

**Type:** `array`

Locales shown in the dashboard language switcher.

Bundled locales:

| Locale | Language |
|--------|----------|
| `en` | English |
| `pl` | Polish |
| `es` | Spanish |
| `fr` | French |
| `de` | German |

Publish translations if you want to customize wording:

```bash
php artisan vendor:publish --tag="lens-for-laravel-translations"
```

### `editor`

**Type:** `string` | **Default:** `vscode` | **Env:** `LENS_FOR_LARAVEL_EDITOR`

Controls "open in editor" links.

| Value | IDE |
|-------|-----|
| `vscode` | Visual Studio Code |
| `cursor` | Cursor |
| `phpstorm` | PhpStorm / JetBrains |
| `sublime` | Sublime Text |
| `none` | Disable editor links |

```text
LENS_FOR_LARAVEL_EDITOR=cursor
```

### `crawl_max_pages`

**Type:** `int` | **Default:** `50` | **Env:** `LENS_FOR_LARAVEL_CRAWL_MAX_PAGES`

Maximum page count for whole-site crawling.

```text
LENS_FOR_LARAVEL_CRAWL_MAX_PAGES=100
```

### `crawler_render_javascript`

**Type:** `bool` | **Default:** `false` | **Env:** `LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT`

When enabled, the crawler attempts to render pages in Chromium and collect links from the hydrated DOM. Use this for SPA and Inertia apps where links are created by React or Vue.

```text
LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=true
```

If browser crawling fails or finds no links, Lens falls back to the default HTTP crawler.

### `scan_wait_ms`

**Type:** `int` | **Default:** `0` | **Env:** `LENS_FOR_LARAVEL_SCAN_WAIT_MS`

Extra delay after network idle before axe-core runs.

```text
LENS_FOR_LARAVEL_SCAN_WAIT_MS=500
```

Useful for:

- Livewire hydration
- Inertia page transitions
- React lazy content
- Vue delayed rendering

### `baseline_path`

**Type:** `string` | **Default:** `storage/app/lens-for-laravel/baseline.json` | **Env:** `LENS_FOR_LARAVEL_BASELINE_PATH`

Default JSON file used by the CLI baseline quality gate.

```text
LENS_FOR_LARAVEL_BASELINE_PATH=.github/lens-baseline.json
```

Relative paths are resolved from the Laravel application base path. Absolute paths are used as provided.

### `ignore_https_errors`

**Type:** `bool` | **Default:** `false` | **Env:** `LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS`

When enabled, Lens ignores HTTPS certificate errors consistently for:

- axe scans, including interactive states
- sitemap and page requests made by the HTTP crawler
- optional JavaScript-rendered crawling in Chromium
- element preview screenshots

```text
LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS=true
```

Use this only for trusted local development environments with self-signed certificates, such as DDEV, Herd, or Laravel Valet. Keep the default `false` for production-like certificate validation. One setting controls both Laravel's HTTP client and every Chromium path used by Lens.

### `ai_enabled`

**Type:** `bool` | **Default:** `true` | **Env:** `LENS_FOR_LARAVEL_AI_ENABLED`

Explicitly enables or disables AI Fix without affecting any scanning feature.

```text
LENS_FOR_LARAVEL_AI_ENABLED=false
```

The setting cannot override runtime compatibility. AI Fix also requires PHP 8.3+, Laravel 12+, and the optional `laravel/ai` package. On unsupported runtimes the dashboard explains that only AI Fix is unavailable.

### `ai_provider`

**Type:** `string` | **Default:** `gemini` | **Env:** `LENS_FOR_LARAVEL_AI_PROVIDER`

AI provider used by the AI Fix Engine.

The provider is contacted only after a user requests a fix. Lens sends the issue details, failing DOM snippet, WCAG tags, and a limited source-code context; it does not send the entire repository.

| Value | Provider | API key |
|-------|----------|---------|
| `gemini` | Google Gemini | `GEMINI_API_KEY` |
| `openai` | OpenAI | `OPENAI_API_KEY` |
| `anthropic` | Anthropic | `ANTHROPIC_API_KEY` |

```text
LENS_FOR_LARAVEL_AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
```

## v3.0.0 Upgrade

v3.0.0 is the current development line. If you published the config before v3.0.0, add these keys manually:

```php
'wcag_version' => env('LENS_FOR_LARAVEL_WCAG_VERSION', '2.0'),
'ai_enabled' => env('LENS_FOR_LARAVEL_AI_ENABLED', true),
```

WCAG 2.0 remains the default so existing dashboard, CLI, baseline, and CI workflows retain their previous scan scope.

## Historical: v2.1.0 Upgrade

If you published the config before v2.1.0, add these keys manually:

```php
'locale' => env('LENS_FOR_LARAVEL_LOCALE', app()->getLocale()),

'fallback_locale' => env('LENS_FOR_LARAVEL_FALLBACK_LOCALE', 'en'),

'supported_locales' => [
    'en' => 'English',
    'pl' => 'Polski',
    'es' => 'Español',
    'fr' => 'Français',
    'de' => 'Deutsch',
],

'baseline_path' => env('LENS_FOR_LARAVEL_BASELINE_PATH', storage_path('app/lens-for-laravel/baseline.json')),

'ignore_https_errors' => env('LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS', false),
```

## Historical: v2.0.0 Upgrade

If you published the config before v2.0.0, add these keys manually:

```php
'crawler_render_javascript' => env('LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT', false),
'scan_wait_ms' => env('LENS_FOR_LARAVEL_SCAN_WAIT_MS', 0),
```
