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

    'editor' => env('LENS_FOR_LARAVEL_EDITOR', 'vscode'),

    'crawl_max_pages' => env('LENS_FOR_LARAVEL_CRAWL_MAX_PAGES', 50),

    'crawler_render_javascript' => env('LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT', false),

    'scan_wait_ms' => env('LENS_FOR_LARAVEL_SCAN_WAIT_MS', 0),

    'ai_provider' => env('LENS_FOR_LARAVEL_AI_PROVIDER', 'gemini'),
];
```

## Options

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

### `ai_provider`

**Type:** `string` | **Default:** `gemini` | **Env:** `LENS_FOR_LARAVEL_AI_PROVIDER`

AI provider used by the AI Fix Engine.

| Value | Provider | API key |
|-------|----------|---------|
| `gemini` | Google Gemini | `GEMINI_API_KEY` |
| `openai` | OpenAI | `OPENAI_API_KEY` |
| `anthropic` | Anthropic | `ANTHROPIC_API_KEY` |

```text
LENS_FOR_LARAVEL_AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
```

## v2.0.0 Upgrade

If you published the config before v2.0.0, add these keys manually:

```php
'crawler_render_javascript' => env('LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT', false),
'scan_wait_ms' => env('LENS_FOR_LARAVEL_SCAN_WAIT_MS', 0),
```
