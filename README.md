# Lens for Laravel — Website

Official marketing website and documentation for **[Lens for Laravel](https://github.com/webcrafts-studio/lens-for-laravel)**, a local-first WCAG accessibility auditor for Laravel applications.

## About

This is the official website for the Lens for Laravel package. It includes:

- Landing page with feature overview, CLI showcase, and dashboard preview
- Full v3.2 documentation covering structural source mapping for Blade, Livewire, React, and Vue alongside Laravel 10–13 compatibility, WCAG 2.0/2.1/2.2 selection, interactive-state scripts, history comparisons, localization, local HTTPS, CI baselines, and optional AI Fix workflows
- Light/dark theme support

The package supports PHP 8.2+ and Laravel 10–13 for core scanning. AI Fix is optional and requires PHP 8.3+, Laravel 12+, and `laravel/ai`.

The package itself lives in a [separate repository](https://github.com/webcrafts-studio/lens-for-laravel).

## Tech Stack

- **Laravel 12** (PHP 8.2+)
- **Tailwind CSS 4** with Vite
- **SQLite** database
- **Pest 4** for testing

## Local Development

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- [Laravel Herd](https://herd.laravel.com) (recommended) or any local server

### Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Running

With Laravel Herd the site is automatically available at `https://lens-for-laravel-website.test`.

Alternatively, use the dev script which starts the server, queue worker, log tail, and Vite in parallel:

```bash
composer run dev
```

### Testing

```bash
php artisan test
```

### Code Formatting

```bash
vendor/bin/pint
```

## License

MIT
