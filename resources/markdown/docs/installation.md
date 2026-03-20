# Installation

## Requirements

Before installing Lens for Laravel, ensure your environment meets these requirements:

- **PHP** 8.2 or higher
- **Laravel** 10, 11, 12, or 13
- **Node.js** 18+ (required by Spatie Browsershot to run Puppeteer)
- **Puppeteer** (headless Chromium driver)

### Installing Puppeteer

Puppeteer must be available on the machine running the scans. Install it as a project dev dependency:

```bash
npm install puppeteer --save-dev
```

Or globally:

```bash
npm install -g puppeteer
```

> **Note:** On first install, Puppeteer downloads a compatible version of Chromium (~170 MB). This is required for Spatie Browsershot to launch a headless browser.

## Installing the Package

```bash
composer require webcrafts-studio/lens-for-laravel --dev
```

> Lens for Laravel is a developer tool and should be installed as a dev dependency. It is disabled by default in all non-local environments.

## Publishing the Config File

To customize the package behaviour, publish the configuration file:

```bash
php artisan vendor:publish --tag="lens-for-laravel-config"
```

This creates `config/lens-for-laravel.php` in your application.

## Setting Environment Variables

Add the following optional variables to your `.env` file:

```text
LENS_FOR_LARAVEL_EDITOR=vscode
LENS_FOR_LARAVEL_CRAWL_MAX_PAGES=50
```

| Variable | Default | Description |
|----------|---------|-------------|
| `LENS_FOR_LARAVEL_EDITOR` | `vscode` | IDE used to open files from the dashboard. |
| `LENS_FOR_LARAVEL_CRAWL_MAX_PAGES` | `50` | Maximum pages scanned in `--crawl` mode. |

## Verifying the Installation

After installing, verify the package is active by visiting the dashboard:

```text
http://your-app.test/lens-for-laravel/dashboard
```

Or run a quick audit from the CLI:

```bash
php artisan lens:audit http://your-app.test
```

If the scan runs and outputs a diagnostic table, installation was successful.

## CI / Production

Lens for Laravel is restricted to local environments by default. In CI pipelines you can extend allowed environments in the config:

```php
// config/lens-for-laravel.php
'enabled_environments' => ['local', 'testing'],
```

> **Warning:** Never enable Lens for Laravel in production. It launches a headless browser on demand, which is resource-intensive, and exposes your application's internal structure.
