# Installation

## Requirements

Before installing Lens for Laravel, make sure the host application has:

- **PHP** 8.2 or higher
- **Laravel** 10, 11, 12, or 13
- **Node.js** recent LTS
- **Puppeteer** for headless Chromium
- **Chromium** available through Puppeteer or your deployment environment

Lens uses Spatie Browsershot to render pages and run axe-core against the browser DOM.

## Install Puppeteer

Install Puppeteer as a local dev dependency in the Laravel app:

```bash
npm install puppeteer --save-dev
```

You can also install it globally if your environment is already configured that way:

```bash
npm install -g puppeteer
```

> On first install, Puppeteer downloads a compatible Chromium build. This is expected.

## Install the Package

```bash
composer require webcrafts-studio/lens-for-laravel --dev
```

Lens is a developer tool and should normally be installed as a dev dependency.

The command above installs every core scanning feature on PHP 8.2+ and Laravel 10–13. It intentionally does not install an AI SDK.

## Optional AI Fix Installation

AI Fix has a narrower compatibility range than the core scanner:

- PHP 8.3 or newer
- Laravel 12 or newer
- optional `laravel/ai` package

Install it separately on a supported application:

```bash
composer require laravel/ai --dev
```

Laravel 10/11 and PHP 8.2 applications continue to receive all non-AI Lens features. The dashboard hides AI Fix actions and displays the exact reason they are unavailable.

## Run Migrations

Run migrations to install scan history tables, v2.1 interactive state metadata, and the v3.0 WCAG version metadata:

```bash
php artisan migrate
```

## Publish Config

```bash
php artisan vendor:publish --tag="lens-for-laravel-config"
```

This creates:

```text
config/lens-for-laravel.php
```

Optionally publish package views:

```bash
php artisan vendor:publish --tag="lens-for-laravel-views"
```

Optionally publish package translations:

```bash
php artisan vendor:publish --tag="lens-for-laravel-translations"
```

## Environment Variables

Add only the options you need:

```text
LENS_FOR_LARAVEL_EDITOR=vscode
LENS_FOR_LARAVEL_LOCALE=en
LENS_FOR_LARAVEL_FALLBACK_LOCALE=en
LENS_FOR_LARAVEL_CRAWL_MAX_PAGES=50
LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=false
LENS_FOR_LARAVEL_SCAN_WAIT_MS=0
LENS_FOR_LARAVEL_WCAG_VERSION=2.0
LENS_FOR_LARAVEL_BASELINE_PATH=storage/app/lens-for-laravel/baseline.json
LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS=false
LENS_FOR_LARAVEL_AI_ENABLED=true
LENS_FOR_LARAVEL_AI_PROVIDER=gemini
LENS_FOR_LARAVEL_AI_OLLAMA_MODEL=
LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT=120
```

| Variable | Default | Description |
|----------|---------|-------------|
| `LENS_FOR_LARAVEL_EDITOR` | `vscode` | IDE used when opening source files from the dashboard. |
| `LENS_FOR_LARAVEL_LOCALE` | app locale | Default dashboard locale. |
| `LENS_FOR_LARAVEL_FALLBACK_LOCALE` | `en` | Fallback locale used when a translation key is missing. |
| `LENS_FOR_LARAVEL_CRAWL_MAX_PAGES` | `50` | Maximum pages discovered in whole-site mode. |
| `LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT` | `false` | Render JavaScript while crawling SPA/Inertia links. |
| `LENS_FOR_LARAVEL_SCAN_WAIT_MS` | `0` | Extra delay after network idle before axe-core runs. |
| `LENS_FOR_LARAVEL_WCAG_VERSION` | `2.0` | Default WCAG standard for dashboard and CLI scans (`2.0`, `2.1`, or `2.2`). |
| `LENS_FOR_LARAVEL_BASELINE_PATH` | `storage/app/lens-for-laravel/baseline.json` | Default file used by the baseline quality gate. |
| `LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS` | `false` | Ignore self-signed HTTPS certificate errors during local scans. |
| `LENS_FOR_LARAVEL_AI_ENABLED` | `true` | Explicitly enable or disable the optional AI Fix integration. |
| `LENS_FOR_LARAVEL_AI_PROVIDER` | `gemini` | AI provider for fix suggestions: `gemini`, `openai`, `anthropic`, or `ollama`. |
| `LENS_FOR_LARAVEL_AI_OLLAMA_MODEL` | SDK default | Exact locally installed Ollama model tag used when the provider is `ollama`. |
| `LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT` | `120` | Ollama request timeout in seconds for slower local generation. |

## AI Provider Keys

AI Fix is optional. After installing `laravel/ai` on a supported runtime, configure a provider only if you want generated fixes:

```text
LENS_FOR_LARAVEL_AI_PROVIDER=gemini
GEMINI_API_KEY=your-key

# or
LENS_FOR_LARAVEL_AI_PROVIDER=openai
OPENAI_API_KEY=your-key

# or
LENS_FOR_LARAVEL_AI_PROVIDER=anthropic
ANTHROPIC_API_KEY=your-key
```

For local generation, install [Ollama](https://ollama.com/download), pull a code-capable model, and point Lens at it:

```bash
ollama pull qwen2.5-coder:7b
```

```text
LENS_FOR_LARAVEL_AI_PROVIDER=ollama
LENS_FOR_LARAVEL_AI_OLLAMA_MODEL=qwen2.5-coder:7b
LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT=120
OLLAMA_URL=http://127.0.0.1:11434
```

The URL is optional when Ollama uses its default local address. With `laravel/ai` 0.3.x, use `OLLAMA_BASE_URL` instead of `OLLAMA_URL` when overriding that address. No API key is needed for the default local Ollama server.

Set `LENS_FOR_LARAVEL_AI_ENABLED=false` to disable AI Fix explicitly while keeping all scanning features enabled.

## Verify Installation

Open the dashboard:

```text
http://your-app.test/lens-for-laravel/dashboard
```

Or run a CLI scan:

```bash
php artisan lens:audit http://your-app.test
```

## CI and Staging

Lens is enabled only in `local` by default. To run it in CI, add the environment explicitly:

```php
'enabled_environments' => ['local', 'testing'],
```

If you enable Lens on staging, protect the routes:

```php
'middleware' => ['web', 'auth'],
```

> Do not expose Lens publicly in production. It launches headless browsers and exposes internal source structure.
