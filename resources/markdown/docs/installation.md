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

## Run Migrations

v2.0.0 includes scan history and scan comparison. Run migrations:

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

## Environment Variables

Add only the options you need:

```text
LENS_FOR_LARAVEL_EDITOR=vscode
LENS_FOR_LARAVEL_CRAWL_MAX_PAGES=50
LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=false
LENS_FOR_LARAVEL_SCAN_WAIT_MS=0
LENS_FOR_LARAVEL_AI_PROVIDER=gemini
```

| Variable | Default | Description |
|----------|---------|-------------|
| `LENS_FOR_LARAVEL_EDITOR` | `vscode` | IDE used when opening source files from the dashboard. |
| `LENS_FOR_LARAVEL_CRAWL_MAX_PAGES` | `50` | Maximum pages discovered in whole-site mode. |
| `LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT` | `false` | Render JavaScript while crawling SPA/Inertia links. |
| `LENS_FOR_LARAVEL_SCAN_WAIT_MS` | `0` | Extra delay after network idle before axe-core runs. |
| `LENS_FOR_LARAVEL_AI_PROVIDER` | `gemini` | AI provider for fix suggestions. |

## AI Provider Keys

AI Fix is optional. Configure a provider only if you want generated fixes:

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
