# CLI Reference

Lens ships one Artisan command:

```bash
php artisan lens:audit {url?*} [options]
```

The CLI uses the same scanner, crawler, source locator, and `sourceType` metadata as the dashboard. It can also create and enforce accessibility baselines for CI.

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

### `--wcag=VERSION`

Choose the WCAG standard version used by axe-core. Supported values are `2.0`, `2.1`, and `2.2`.

```bash
php artisan lens:audit https://your-app.test --wcag=2.2 --aa
```

WCAG versions are cumulative: 2.1 includes the applicable 2.0 rules, and 2.2 includes the applicable 2.0 and 2.1 rules. WCAG 2.0 is the default so existing commands and CI workflows keep their previous scan scope.

The version option is separate from the level filters. `--wcag=2.2` chooses the standard, while `--a`, `--aa`, or `--all` chooses which conformance levels are reported.

### `--crawl`

Discover internal pages and scan them.

```bash
php artisan lens:audit https://your-app.test --crawl
```

Use `LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=true` for SPA/Inertia link discovery.

### `--states=PATH`

Execute a reusable interactive-state script before scanning each named state:

```bash
php artisan lens:audit https://your-app.test --states=tests/accessibility/navigation.states
```

Paths relative to the Laravel project root and absolute paths are supported. The CLI uses the same script grammar and limits as the dashboard recorder.

State mode requires exactly one URL and cannot be combined with `--crawl`. It can be combined with `--wcag`, `--a`, `--aa`, `--all`, `--threshold`, `--baseline`, and `--fail-on-new`.

Example script:

```text
state: Navigation open
click: [data-menu-button]

state: Form validation
type: input[name="email"] => invalid@example.test
click: button[type="submit"]
wait: 300
```

The diagnostic table includes the state label. Baseline fingerprints preserve it, so the same selector in two different states remains two separate issues.

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

### `--baseline`

Write the current filtered violations to a baseline file and exit successfully.

```bash
php artisan lens:audit https://your-app.test --crawl --baseline
```

The baseline includes issue fingerprints and scan metadata. Use this after reviewing and accepting the current accessibility state of an existing project.

`--baseline` respects WCAG level filters:

```bash
php artisan lens:audit https://your-app.test --crawl --aa --baseline
```

### `--baseline-file=PATH`

Use a custom baseline path instead of the configured default.

```bash
php artisan lens:audit https://your-app.test --crawl --baseline --baseline-file=.github/lens-baseline.json
```

Relative paths are resolved from the Laravel application base path.

### `--fail-on-new`

Compare current violations against the baseline and fail only when new violations appear.

```bash
php artisan lens:audit https://your-app.test --crawl --fail-on-new
```

Exit behavior:

| Result | Exit Code |
|--------|-----------|
| No new violations | `0` |
| One or more new violations | `1` |
| Baseline file missing or invalid | `1` |

`--baseline` and `--fail-on-new` cannot be used together.

## Examples

```bash
# scan APP_URL
php artisan lens:audit

# focus on compliance target
php artisan lens:audit https://your-app.test --wcag=2.2 --aa

# scan UI exposed by interactions
php artisan lens:audit https://your-app.test --states=tests/accessibility/navigation.states --wcag=2.2 --aa

# crawl site and fail on any A/AA issue
php artisan lens:audit https://your-app.test --crawl --aa --threshold=0

# scan selected routes
php artisan lens:audit \
  https://your-app.test/login \
  https://your-app.test/register \
  https://your-app.test/dashboard \
  --aa --threshold=0

# create a reviewed baseline
php artisan lens:audit https://your-app.test --crawl --aa --baseline

# fail only when new violations appear
php artisan lens:audit https://your-app.test --crawl --aa --fail-on-new

# store the baseline in a custom path
php artisan lens:audit https://your-app.test --crawl --baseline-file=.github/lens-baseline.json --fail-on-new
```

## Baseline Fingerprints

Lens compares issues using stable fingerprints built from:

- axe rule ID
- normalized URL path and query
- interactive state label
- CSS selector
- source file
- source type

The URL scheme and host are ignored during fingerprinting. This allows a baseline created from `https://app.test/about` to match a CI scan running against `http://127.0.0.1:8000/about`.

## Baseline Workflow

Use this flow for projects that already have accessibility issues:

1. Run a full scan locally.
2. Review the current violations.
3. Save the reviewed state with `--baseline`.
4. Commit the baseline file if you want CI to use it.
5. Run `--fail-on-new` in CI.

Create a new reviewed baseline when switching between WCAG 2.0, 2.1, and 2.2. A newer standard can legitimately introduce additional violations.

Example:

```bash
php artisan lens:audit https://your-app.test --crawl --aa --baseline --baseline-file=.github/lens-baseline.json
```

Then in CI:

```bash
php artisan lens:audit http://127.0.0.1:8000 --crawl --aa --fail-on-new --baseline-file=.github/lens-baseline.json
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

## GitHub Actions Baseline Example

```yaml
name: Accessibility Regression Audit

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

      - name: Run accessibility regression audit
        run: php artisan lens:audit http://127.0.0.1:8000 --crawl --aa --fail-on-new --baseline-file=.github/lens-baseline.json
        env:
          APP_ENV: testing
```
