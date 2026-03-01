# CLI Reference

Lens for Laravel ships a single Artisan command: `lens:audit`. It is the primary interface for running accessibility scans from the terminal and CI/CD pipelines.

## Command Signature

```bash
php artisan lens:audit {url*} [options]
```

## Arguments

### `url*`

**Type:** `string[]` | **Required:** No (defaults to `APP_URL`)

One or more URLs to audit. When omitted, Lens defaults to your application's `APP_URL` config value.

```bash
# Single URL (defaults to APP_URL if omitted)
php artisan lens:audit

# Explicit single URL
php artisan lens:audit https://your-app.test

# Multiple URLs
php artisan lens:audit https://your-app.test https://your-app.test/about
```

## Options

### `--a`

Report **only WCAG Level A** violations.

Level A represents the minimum level of accessibility compliance. These are critical blockers — users with disabilities cannot access the affected content at all.

```bash
php artisan lens:audit https://your-app.test --a
```

---

### `--aa`

Report **WCAG Level A and Level AA** violations.

Level AA is the standard compliance target required by most accessibility laws and regulations (ADA, EN 301 549, European Accessibility Act, Section 508).

```bash
php artisan lens:audit https://your-app.test --aa
```

---

### `--all`

Report **all violation levels** including AAA and best-practice rules. This is the **default** behaviour when no level flag is provided.

```bash
php artisan lens:audit https://your-app.test --all
# equivalent to:
php artisan lens:audit https://your-app.test
```

---

### `--threshold=N`

**Type:** `int` | **Default:** `0`

Sets a quality gate: the command exits with code `1` if the total violation count **exceeds** the threshold. Use this in CI pipelines to block deployments that introduce new accessibility issues.

```bash
# Fail if there are any violations (threshold = 0 means "no violations allowed")
php artisan lens:audit https://your-app.test --aa --threshold=0

# Allow up to 5 violations before failing
php artisan lens:audit https://your-app.test --aa --threshold=5
```

**Exit code behaviour:**

| Violations | Threshold | Exit Code |
|-----------|-----------|-----------|
| 0 | 0 | `0` — Pass |
| 3 | 0 | `1` — Fail |
| 3 | 5 | `0` — Pass |
| 6 | 5 | `1` — Fail |

---

### `--crawl`

Enable **WHOLE_WEBSITE** mode. Lens discovers all internal pages via sitemap seeding and BFS link traversal, then scans each page.

```bash
php artisan lens:audit https://your-app.test --crawl
```

The maximum number of pages is controlled by `crawl_max_pages` (default: 50). See [Scanning Modes](/docs/scanning-modes) for the full crawl strategy.

## Verbosity

Use Laravel's standard `-v` / `-vv` / `-vvv` flags to increase output verbosity. At `-v`, Lens outputs the full HTML node for each violation.

```bash
php artisan lens:audit https://your-app.test -v
```

## Complete Examples

```bash
# Minimal: scan app URL, report all levels
php artisan lens:audit

# Targeted level filter
php artisan lens:audit https://your-app.test --aa

# Full site crawl with CI quality gate
php artisan lens:audit https://your-app.test --aa --crawl --threshold=0

# Multiple specific pages
php artisan lens:audit \
  https://your-app.test/login \
  https://your-app.test/register \
  https://your-app.test/dashboard \
  --aa --threshold=0

# Verbose output to inspect full HTML nodes
php artisan lens:audit https://your-app.test --aa -v
```

## GitHub Actions Integration

```yaml
name: Accessibility Audit

on: [push, pull_request]

jobs:
  a11y:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'

      - name: Install dependencies
        run: composer install --no-interaction

      - name: Install Puppeteer
        run: npm install puppeteer --save-dev

      - name: Start dev server
        run: php artisan serve --port=8000 &
        env:
          APP_ENV: testing

      - name: Run accessibility audit
        run: php artisan lens:audit http://localhost:8000 --aa --threshold=0
```
