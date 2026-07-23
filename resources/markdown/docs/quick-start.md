# Quick Start

Get your first accessibility audit running in a few minutes.

## First CLI Audit

Run a scan against your local app:

```bash
php artisan lens:audit http://your-app.test
```

This uses WCAG 2.0 by default. To scan the cumulative WCAG 2.2 rule set:

```bash
php artisan lens:audit http://your-app.test --wcag=2.2
```

If you omit the URL, Lens defaults to your Laravel `APP_URL`:

```bash
php artisan lens:audit
```

Lens launches Chromium, renders the page, injects axe-core, collects violations, maps source locations when possible, and prints a diagnostic report.

To scan named UI states from a recorded or hand-written script:

```bash
php artisan lens:audit http://your-app.test --states=tests/accessibility/navigation.states
```

## First Dashboard Audit

Open:

```text
http://your-app.test/lens-for-laravel/dashboard
```

Then:

1. Enter a URL from the same host as `APP_URL`.
2. Choose **Single URL**, **Multiple URLs**, **Whole Website**, or **Interactive States**.
3. Choose WCAG 2.0, 2.1, or 2.2. WCAG 2.0 is the default.
4. Run the scan.
5. Inspect WCAG level cards and violation details.
6. Preview the failing element.
7. Open the source file from `SRC_LOC`.
8. Optionally request an individual AI fix, then review or edit the proposal before applying it.
9. On v3.1, use **Fix All A** or **Fix All AA** to open a progressive review queue for every located issue at that level.

Fix All does not write every suggestion automatically. The modal opens immediately, generates up to three suggestions concurrently, and keeps an independent state for each item. You can move to any ready proposal while other items still show loaders, then edit, accept, reject, or retry each fix separately.

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
  "sourceType": "vue",
  "stateLabel": null
}
```

`sourceType` can be:

- `blade`
- `react`
- `vue`
- `null` when no source location is found

`stateLabel` is set when an issue comes from an interactive state scan.

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

For existing projects with known accessibility debt, use a baseline instead:

```bash
php artisan lens:audit http://your-app.test --crawl --baseline
php artisan lens:audit http://your-app.test --crawl --fail-on-new
```

The first command writes the reviewed current state. The second command fails only when new violations appear.

## First Interactive State Scan

Use interactive states when a violation appears only after opening a menu, submitting an invalid form, expanding a tab, or triggering another UI state.

In the dashboard, choose **Interactive States**, open the recorder, interact with the page, and send the generated script back to Lens.

You can also write a script manually:

```text
state: Navigation open
click: [data-menu-button]

state: Form validation
type: input[name="email"] => invalid@example.test
click: button[type="submit"]
wait: 300
```
