# CLI Reference

Lens ships one Artisan command:

```bash
php artisan lens:audit {url?*} [options]
```

The CLI uses the same scanner, crawler, source locator, and `sourceType` metadata as the dashboard.

## Arguments

### `url?*`

One or more URLs to audit. If omitted, Lens uses `APP_URL`.

```bash
php artisan lens:audit
php artisan lens:audit https://your-app.test
php artisan lens:audit https://your-app.test https://your-app.test/about
```

## Options

### `--a`

Report only WCAG Level A violations.

```bash
php artisan lens:audit https://your-app.test --a
```

### `--aa`

Report WCAG Level A and AA violations.

```bash
php artisan lens:audit https://your-app.test --aa
```

### `--all`

Report A, AA, AAA, and best-practice rules. This is the default.

```bash
php artisan lens:audit https://your-app.test --all
```

### `--crawl`

Discover internal pages and scan them.

```bash
php artisan lens:audit https://your-app.test --crawl
```

Use `LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=true` for SPA/Inertia link discovery.

### `--threshold=N`

Fail with exit code `1` when the violation count exceeds the threshold.

```bash
php artisan lens:audit https://your-app.test --aa --threshold=0
```

| Violations | Threshold | Exit Code |
|-----------|-----------|-----------|
| 0 | 0 | `0` |
| 3 | 0 | `1` |
| 3 | 5 | `0` |
| 6 | 5 | `1` |

## Examples

```bash
# scan APP_URL
php artisan lens:audit

# focus on compliance target
php artisan lens:audit https://your-app.test --aa

# crawl site and fail on any A/AA issue
php artisan lens:audit https://your-app.test --crawl --aa --threshold=0

# scan selected routes
php artisan lens:audit \
  https://your-app.test/login \
  https://your-app.test/register \
  https://your-app.test/dashboard \
  --aa --threshold=0
```

## GitHub Actions Example

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

      - name: Install PHP dependencies
        run: composer install --no-interaction

      - name: Install Puppeteer
        run: npm install puppeteer --save-dev

      - name: Prepare app
        run: |
          cp .env.example .env
          php artisan key:generate
          php artisan migrate --force

      - name: Start dev server
        run: php artisan serve --host=127.0.0.1 --port=8000 &
        env:
          APP_ENV: testing
          APP_URL: http://127.0.0.1:8000

      - name: Run accessibility audit
        run: php artisan lens:audit http://127.0.0.1:8000 --aa --threshold=0
        env:
          APP_ENV: testing
```
