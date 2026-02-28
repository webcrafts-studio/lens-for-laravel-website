# Config File

After publishing with `php artisan vendor:publish --tag="laravel-lens-config"`, you'll find the configuration at `config/laravel-lens.php`.

## Full Config Reference

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    | The URL prefix for the Laravel Lens dashboard and API routes.
    | Default: /laravel-lens/dashboard
    */
    'route_prefix' => 'laravel-lens',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Middleware applied to all Lens routes. Add 'auth' to restrict
    | dashboard access to authenticated users.
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Enabled Environments
    |--------------------------------------------------------------------------
    | Lens returns a 403 Forbidden response outside these environments.
    | Keep 'local' only in most cases.
    */
    'enabled_environments' => ['local'],

    /*
    |--------------------------------------------------------------------------
    | Editor
    |--------------------------------------------------------------------------
    | The IDE used to generate "open in editor" links for Blade files.
    | Supported: 'vscode', 'cursor', 'phpstorm', 'sublime', 'none'
    | Env: LARAVEL_LENS_EDITOR
    */
    'editor' => env('LARAVEL_LENS_EDITOR', 'vscode'),

    /*
    |--------------------------------------------------------------------------
    | Crawl Max Pages
    |--------------------------------------------------------------------------
    | Maximum number of pages to discover and scan in WHOLE_WEBSITE mode.
    | Env: LARAVEL_LENS_CRAWL_MAX_PAGES
    */
    'crawl_max_pages' => env('LARAVEL_LENS_CRAWL_MAX_PAGES', 50),

];
```

## Option Reference

### `route_prefix`

**Type:** `string` | **Default:** `'laravel-lens'`

Changes the URL prefix for all Lens routes. With the default value, the dashboard is at `/laravel-lens/dashboard`.

```php
'route_prefix' => 'a11y',
// Dashboard → /a11y/dashboard
```

---

### `middleware`

**Type:** `array` | **Default:** `['web']`

Middleware applied to all Lens routes. To restrict the dashboard to logged-in users, add the `auth` middleware:

```php
'middleware' => ['web', 'auth'],
```

---

### `enabled_environments`

**Type:** `array` | **Default:** `['local']`

Lens checks `app()->environment()` against this list. Requests from disallowed environments receive a `403 Forbidden` response.

```php
// Allow in CI pipeline too
'enabled_environments' => ['local', 'testing'],
```

> **Warning:** Do not add `'production'` to this list. Lens launches headless browsers on demand and is not designed for production traffic.

---

### `editor`

**Type:** `string` | **Default:** `'vscode'` | **Env:** `LARAVEL_LENS_EDITOR`

Controls the URL scheme used when clicking "open in editor" links in the dashboard.

| Value | URL Scheme | IDE |
|-------|-----------|-----|
| `vscode` | `vscode://file/{path}:{line}` | Visual Studio Code |
| `cursor` | `cursor://file/{path}:{line}` | Cursor |
| `phpstorm` | `phpstorm://open?file={path}&line={line}` | PhpStorm / JetBrains |
| `sublime` | `subl://{path}:{line}` | Sublime Text |
| `none` | *(disabled)* | No links rendered |

Set via environment variable to avoid committing IDE preferences:

```text
LARAVEL_LENS_EDITOR=cursor
```

---

### `crawl_max_pages`

**Type:** `int` | **Default:** `50` | **Env:** `LARAVEL_LENS_CRAWL_MAX_PAGES`

Maximum number of pages that will be discovered and scanned in `WHOLE_WEBSITE` mode. Higher values produce more thorough coverage but significantly increase scan time.

```text
LARAVEL_LENS_CRAWL_MAX_PAGES=100
```

> **Performance note:** Each page requires a full headless browser launch (Browsershot + Axe-core). Scanning 50 pages typically takes 2–5 minutes depending on page complexity and server speed.
