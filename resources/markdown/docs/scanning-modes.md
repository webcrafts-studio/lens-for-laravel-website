# Scanning Modes

Lens supports three scan modes in both the dashboard and CLI.

## Single URL

Audit one page:

```bash
php artisan lens:audit https://your-app.test
```

How it works:

1. Browsershot launches Chromium.
2. The page is rendered and hydrated.
3. Optional `scan_wait_ms` delay is applied.
4. axe-core runs in the browser.
5. Violations are mapped to Blade, React, or Vue source files when possible.

## Multiple URLs

Pass several URLs:

```bash
php artisan lens:audit \
  https://your-app.test \
  https://your-app.test/about \
  https://your-app.test/contact
```

Lens scans each URL in sequence, skips failed pages, aggregates issues, and includes the source URL on every issue.

## Whole Website Crawl

Discover and scan internal pages:

```bash
php artisan lens:audit https://your-app.test --crawl
```

### Crawl Strategy

Lens seeds URLs from:

```text
/sitemap.xml
/sitemap_index.xml
/sitemaps/sitemap.xml
```

Then it follows internal `<a href>` links until the queue is empty or `crawl_max_pages` is reached.

By default, link discovery uses Laravel's HTTP client and parses the initial HTML. This is fast and works well for server-rendered pages.

## SPA and Inertia Crawling

For React, Vue, or Inertia apps where links appear only after JavaScript renders, enable browser-based link discovery:

```text
LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=true
```

With this enabled, Lens tries to render each crawled page in Chromium and read links from the hydrated DOM. If browser crawling fails or finds no links, Lens falls back to the HTTP crawler.

## What Gets Crawled

Lens follows only internal HTML pages. It skips:

- external domains
- static assets: images, CSS, JS, fonts, PDFs, archives
- `javascript:`, `mailto:`, `tel:`, and fragment-only links
- XML, JSON, text, and CSV endpoints

## Crawl Limit

```text
LENS_FOR_LARAVEL_CRAWL_MAX_PAGES=100
```

Or in config:

```php
'crawl_max_pages' => env('LENS_FOR_LARAVEL_CRAWL_MAX_PAGES', 100),
```

## WCAG Level Filtering

| Flag | Levels Reported |
|------|----------------|
| `--a` | WCAG Level A only |
| `--aa` | WCAG Level A + AA |
| `--all` | A + AA + AAA + best-practice rules |

```bash
php artisan lens:audit https://your-app.test --aa --crawl
```

## CI Quality Gate

```bash
php artisan lens:audit https://staging.app.test --aa --threshold=0
```

| Violations | Threshold | Exit Code |
|-----------|-----------|-----------|
| 0 | 0 | `0` |
| 3 | 0 | `1` |
| 3 | 5 | `0` |
| 6 | 5 | `1` |

## GitHub Actions Example

```yaml
- name: Run accessibility audit
  run: php artisan lens:audit ${{ env.APP_URL }} --aa --threshold=0
```
