# Scanning Modes

Lens for Laravel supports three distinct scan modes, selectable both from the CLI and the dashboard.

## Single URL

Audit one specific page. This is the default mode when you pass a single URL argument.

```bash
php artisan lens:audit https://your-app.test
```

**When to use:** Quick checks on a specific page, focused debugging, or verifying a fix on a particular route.

**How it works:**

1. Browsershot launches a headless Chromium instance and navigates to the URL
2. Axe-core 4.8.2 is fetched from CDN and injected into the page
3. `axe.run()` executes with all WCAG A, AA, AAA, and best-practice rules enabled
4. Results are mapped through FileLocator to identify Blade source locations
5. The diagnostic table is rendered to the terminal

## Multiple URLs

Pass multiple URLs as space-separated arguments to scan them all in sequence.

```bash
php artisan lens:audit \
  https://your-app.test \
  https://your-app.test/about \
  https://your-app.test/contact
```

**How it works:**

- A progress bar tracks scan progress across all URLs
- Failed pages (network errors, timeouts) are skipped gracefully — the scan continues
- Issues from all pages are aggregated into a single diagnostic report
- Each violation includes its source URL
- Returns a tuple of `[Collection<Issue>, array $failedUrls]`

**Identifying which page a violation came from:**

In the dashboard, multi-URL results include the source URL in each violation card. From the CLI, violations are grouped by page.

## Whole Website (Crawl)

Discover and scan your entire site automatically using BFS link traversal and sitemap seeding.

```bash
php artisan lens:audit https://your-app.test --crawl
```

**When to use:** Full compliance audits, pre-release checks, or initial onboarding assessments.

### Crawl Strategy

The crawler uses a two-phase discovery strategy:

**Phase 1 — Sitemap seeding (fast):**

Lens attempts to read your sitemap at these paths in order:

```text
/sitemap.xml
/sitemap_index.xml
/sitemaps/sitemap.xml
```

If a sitemap is found, all URLs within it are added to the crawl queue immediately. Nested `<sitemapindex>` elements are parsed recursively.

**Phase 2 — BFS link traversal:**

Starting from the base URL, Lens performs breadth-first search:

```text
queue = [baseUrl]
visited = []

while queue not empty and visited.count < max_pages:
    url = queue.shift()
    skip if already visited
    visited.add(url)

    html = http_get(url)          // plain HTTP, NOT Browsershot
    links = extract_a_href(html)

    for each link:
        if internal_page(link) and not visited and not in queue:
            queue.push(link)
```

> **Note:** Link discovery uses plain HTTP (not headless Chromium) because it is orders of magnitude faster. Browsershot is only used during the Axe-core scan phase.

### What Gets Crawled

The crawler follows only **internal HTML pages**. It automatically skips:

- External domains
- Static assets: `.jpg`, `.png`, `.gif`, `.svg`, `.css`, `.js`, `.woff`, `.ttf`, `.pdf`, `.zip`
- Non-navigable hrefs: `javascript:`, `mailto:`, `tel:`, `#fragment`
- XML, JSON, and API endpoints

### Limiting Crawl Depth

The max page count is controlled by `crawl_max_pages` (default: 50):

```bash
LENS_FOR_LARAVEL_CRAWL_MAX_PAGES=100 php artisan lens:audit https://your-app.test --crawl
```

Or permanently in `config/lens-for-laravel.php`:

```php
'crawl_max_pages' => env('LENS_FOR_LARAVEL_CRAWL_MAX_PAGES', 100),
```

## Level Filtering

All three scan modes support WCAG level filtering. Without a filter, all levels (A, AA, AAA, and best practice) are scanned and reported.

| Flag | Levels Reported |
|------|----------------|
| `--a` | WCAG 2.x Level A only |
| `--aa` | WCAG 2.x Level A + AA |
| `--all` | A + AA + AAA + Best Practice *(default)* |

```bash
# Report only the most critical violations
php artisan lens:audit https://your-app.test --a

# Standard compliance target
php artisan lens:audit https://your-app.test --aa --crawl
```

## CI/CD Quality Gate

Use `--threshold=N` to control the exit code:

```bash
php artisan lens:audit https://staging.app.test --aa --threshold=0
echo $?   # 1 if violations > 0, else 0
```

| Scenario | Exit Code |
|----------|-----------|
| 0 violations, threshold=0 | `0` |
| 3 violations, threshold=0 | `1` |
| 3 violations, threshold=5 | `0` |
| 6 violations, threshold=5 | `1` |

### GitHub Actions Example

```yaml
- name: Run accessibility audit
  run: |
    php artisan lens:audit ${{ env.APP_URL }} --aa --threshold=0
```
