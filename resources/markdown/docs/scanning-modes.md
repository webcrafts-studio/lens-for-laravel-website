# Scanning Modes

Lens supports single URL, multiple URL, and whole-site scans in both the dashboard and CLI. The dashboard also supports interactive state scans for UI that appears after user interaction.

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

## Interactive State Scans

Interactive state scans run axe-core after Lens performs browser actions. Use them for accessibility issues that only appear after UI interaction.

Common examples:

- mobile navigation after the menu is opened
- validation errors after submitting a form
- modal dialogs after clicking a trigger
- tabs and accordions after selecting a panel
- dropdowns and comboboxes after expanding the control
- Livewire, React, Vue, or Inertia states that are not visible on initial load

Interactive state scans are available in the dashboard through the **Interactive States** scan mode.

## State Recorder

The dashboard includes a visual recorder. It opens the target page in a recorder view, lets you interact with it, and generates a reusable interaction script.

Typical workflow:

1. Open the dashboard.
2. Select **Interactive States**.
3. Enter a target URL from the same host as `APP_URL`.
4. Click **Record**.
5. Interact with the target page.
6. Create named states as you move through the flow.
7. Send the generated script back to Lens.
8. Run the scan.

## Interaction Script Format

Interactive scripts are plain text, so they can be copied, reviewed, committed, and reused.

```text
state: Navigation open
click: [data-menu-button]

state: Form validation
type: input[name="email"] => invalid@example.test
click: button[type="submit"]
wait: 300
```

Each `state:` starts a named state. The actions below that line are executed before axe-core scans the page.

### Supported Actions

| Action | Format | Description |
|--------|--------|-------------|
| `click` | `click: selector` | Clicks an element and prevents navigation/submission during the action. |
| `type` | `type: selector => value` | Sets an input value and dispatches `input` and `change` events. |
| `select` | `select: selector => value` | Sets a select value and dispatches `input` and `change` events. |
| `check` | `check: selector` | Checks a checkbox or radio input. |
| `uncheck` | `uncheck: selector` | Unchecks a checkbox. |
| `wait` | `wait: 300` | Waits the given number of milliseconds. |

You can also use space-based shorthand:

```text
state "Search results"
type input[name="q"] "pricing"
click button[type="submit"]
```

For `type` and `select`, the preferred delimiter is `=>`, but `|` is also accepted:

```text
select: select[name="country"] | PL
```

### Script Limits

Interactive scripts are validated before execution:

| Limit | Value |
|-------|-------|
| Maximum states | 10 |
| Maximum actions | 30 |
| Maximum state label length | 80 characters |
| Maximum selector length | 500 characters |
| Maximum typed/selected value length | 1000 characters |
| Maximum wait duration | 5000ms |

Invalid scripts return a validation error before any scan runs.

### State Labels

Issues found during interactive scans include `stateLabel`:

```json
{
  "id": "button-name",
  "selector": ".modal button.close",
  "stateLabel": "Modal open"
}
```

History, PDF reports, and scan comparison preserve this state label. The same selector can therefore be treated as a different issue when it appears in different UI states.

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

Choose the standard version separately with `--wcag=2.0`, `--wcag=2.1`, or `--wcag=2.2`. WCAG 2.0 is the backward-compatible default. Later versions run cumulative axe-core tags for the earlier standard plus the newer success criteria.

| Flag | Levels Reported |
|------|----------------|
| `--a` | WCAG Level A only |
| `--aa` | WCAG Level A + AA |
| `--all` | A + AA + AAA + best-practice rules |

```bash
php artisan lens:audit https://your-app.test --wcag=2.2 --aa --crawl
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

## Baseline Quality Gate

Use a baseline when the project already has known violations and CI should fail only on new regressions.

Create or refresh the baseline after reviewing the current scan:

```bash
php artisan lens:audit https://your-app.test --crawl --baseline
```

Compare the current scan against the baseline:

```bash
php artisan lens:audit https://your-app.test --crawl --fail-on-new
```

Use a custom baseline file when you want to commit it into a specific path:

```bash
php artisan lens:audit https://your-app.test --crawl --fail-on-new --baseline-file=.github/lens-baseline.json
```

Baseline fingerprints include the rule, normalized URL path, state label, selector, source file, and source type. The host and scheme are normalized so local and CI URLs can be compared.
