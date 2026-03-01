# Quick Start

Get your first accessibility audit running in under two minutes.

## Your First Audit

Run a single-URL audit against your local app:

```bash
php artisan lens:audit http://your-app.test
```

Lens will launch a headless browser, inject Axe-core into the page, collect all violations, attempt to locate each violation in your Blade files, and render a diagnostic table.

### Example Output

```text
 DIAGNOSTIC REPORT
 TOTAL_VIOLATIONS: 3

 ┌───────────┬──────────────┬───────────────────────────────────────────────┐
 │ Level     │ Count        │                                               │
 ├───────────┼──────────────┤                                               │
 │ A LEVEL   │ 3            │                                               │
 │ AA LEVEL  │ 0            │                                               │
 │ AAA LEVEL │ 17           │                                               │
 │ OTHER     │ 81           │                                               │
 └───────────┴──────────────┴───────────────────────────────────────────────┘

 [WCAG A] link-name — CRITICAL
 Ensures links have discernible text
 >>> partials/footer.blade.php:112
 <a href="" class="footer__social">
   <i class="fa-brands fa-linkedin" aria-hidden="true"></i>
 </a>
```

## Filtering by WCAG Level

For most projects, focus on Level A and AA violations first — these are the most impactful and legally relevant:

```bash
php artisan lens:audit http://your-app.test --aa
```

This reports only WCAG A and AA violations, suppressing AAA and best-practice noise.

## Accessing the Dashboard

The dashboard provides a visual interface for running audits and exploring violations:

```text
http://your-app.test/lens-for-laravel/dashboard
```

From the dashboard you can:

1. Enter a target URL and choose a scan mode (Single, Multiple, or Whole Website)
2. Click **EXECUTE** to run the audit
3. View the diagnostic report with level-based cards
4. Filter by WCAG level
5. Click **AI FIX** on any violation to get a Gemini-generated fix
6. Export a PDF report

## Understanding the Output

### Violation Cards

The summary cards at the top of the report show the violation count grouped by WCAG level:

- **A LEVEL** — Critical blockers (red highlight). Fix these first.
- **AA LEVEL** — Standard compliance target.
- **AAA LEVEL** — Best-effort improvements.
- **OTHER** — Axe-core best-practice rules outside the WCAG framework.

### Each Violation Shows

- The **WCAG rule ID** (e.g., `link-name`, `button-name`, `image-alt`)
- **Impact level**: `critical`, `serious`, `moderate`, or `minor`
- **Description** of what the rule checks
- **Failing HTML node** — the exact element that failed
- **Source location** — the Blade file and line number (when detected)
- **CSS selector** — the full DOM path to the element

## Setting a Quality Gate

In CI/CD pipelines, use `--threshold` to fail the build if violations exceed a limit:

```bash
php artisan lens:audit http://your-app.test --aa --threshold=0
```

An exit code of `1` is returned when the violation count exceeds the threshold. Exit code `0` means the gate passed.
