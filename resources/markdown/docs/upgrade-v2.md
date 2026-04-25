# Upgrade to v2.0.0

Lens v2.0.0 is a major frontend support release.

## What's New

- React source locator
- React AI Fix
- Vue source locator
- Vue AI Fix
- Inertia-friendly source discovery
- Livewire-friendly scan delay
- `sourceType` metadata
- scan history
- scan comparison
- SPA JavaScript crawler option
- expanded AI Fix safety checks

## Upgrade Steps

Update the package:

```bash
composer update webcrafts-studio/lens-for-laravel
```

Run migrations:

```bash
php artisan migrate
```

If you publish package config, republish it:

```bash
php artisan vendor:publish --tag="lens-for-laravel-config"
```

Or manually add the new v2 options:

```php
'crawler_render_javascript' => env('LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT', false),
'scan_wait_ms' => env('LENS_FOR_LARAVEL_SCAN_WAIT_MS', 0),
```

## New Issue Field

Issues can now include:

```json
{
  "sourceType": "react"
}
```

Possible values:

- `blade`
- `react`
- `vue`
- `null`

If you consume `/scan` responses directly, update your integration to tolerate this new field.

## SPA Crawling

For React, Vue, or Inertia apps with client-rendered links:

```text
LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=true
```

## Hydration Delay

For Livewire, Inertia, React, or Vue pages that render shortly after network idle:

```text
LENS_FOR_LARAVEL_SCAN_WAIT_MS=500
```

## AI Fix Scope

AI Fix can now write to:

```text
resources/views/**/*.blade.php
resources/js/**/*.js
resources/js/**/*.jsx
resources/js/**/*.ts
resources/js/**/*.tsx
resources/js/**/*.vue
```

Review generated fixes before committing them.
