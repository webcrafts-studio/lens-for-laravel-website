# Quick Start

Get your first accessibility audit running in a few minutes.

## First CLI Audit

Run a scan against your local app:

```bash
php artisan lens:audit http://your-app.test
```

If you omit the URL, Lens defaults to your Laravel `APP_URL`:

```bash
php artisan lens:audit
```

Lens launches Chromium, renders the page, injects axe-core, collects violations, maps source locations when possible, and prints a diagnostic report.

## First Dashboard Audit

Open:

```text
http://your-app.test/lens-for-laravel/dashboard
```

Then:

1. Enter a URL from the same host as `APP_URL`.
2. Choose **Single URL**, **Multiple URLs**, or **Whole Website**.
3. Run the scan.
4. Inspect WCAG level cards and violation details.
5. Preview the failing element.
6. Open the source file from `SRC_LOC`.
7. Optionally request an AI fix.

## Example Issue Output

```json
{
  "id": "image-alt",
  "impact": "critical",
  "description": "Images must have alternate text",
  "htmlSnippet": "<img class=\"logo\" src=\"/logo.png\">",
  "selector": ".logo",
  "tags": ["wcag2a"],
  "url": "http://your-app.test",
  "fileName": "js/Components/Logo.vue",
  "lineNumber": 12,
  "sourceType": "vue"
}
```

`sourceType` can be:

- `blade`
- `react`
- `vue`
- `null` when no source location is found

## Focus on A and AA First

For most teams, Level A and AA issues are the highest-priority set:

```bash
php artisan lens:audit http://your-app.test --aa
```

## Whole-Site Scan

```bash
php artisan lens:audit http://your-app.test --crawl --aa
```

If your app is an SPA or Inertia app where links are rendered after hydration, enable JavaScript crawling:

```text
LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=true
```

## Hydration Delay

For Livewire, Inertia, React, or Vue screens that finish rendering shortly after network idle, add a scan delay:

```text
LENS_FOR_LARAVEL_SCAN_WAIT_MS=500
```

## Quality Gate

Use `--threshold` in CI:

```bash
php artisan lens:audit http://your-app.test --aa --threshold=0
```

Exit code `1` means the violation count exceeded the threshold. Exit code `0` means the gate passed.
