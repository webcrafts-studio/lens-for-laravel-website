# Dashboard

The Lens dashboard is the visual interface for running scans, reviewing violations, requesting AI fixes, exporting reports, and browsing scan history.

## Accessing the Dashboard

```text
http://your-app.test/lens-for-laravel/dashboard
```

The prefix is configurable through `route_prefix`.

## Scanner Tab

The scanner tab supports:

- **Single URL** scans
- **Multiple URLs** scans
- **Whole Website** crawl scans

For whole-site scans, the dashboard first calls the crawl endpoint, then scans each discovered URL.

## Diagnostic Report

The report includes:

- total violation count
- WCAG A, AA, AAA, and other counts
- issue impact and rule ID
- failing DOM node
- source location
- source type badge: `blade`, `react`, or `vue`
- CSS selector
- documentation link
- preview button
- AI Fix button when a supported source file is found

## Source Locations

Located issues show:

```text
react js/Pages/Dashboard.tsx:42
vue js/Components/Button.vue:12
blade layouts/app.blade.php:18
```

Click the source location to open the file in your configured editor.

## Element Preview

Click the preview icon to capture a screenshot with the failing element highlighted. This is useful when the selector is long or the failing element is visually hard to find.

## AI Fix Workflow

1. Click **AI FIX** on a located issue.
2. Lens reads source context around the issue.
3. The configured AI provider returns `fixedCode` and an explanation.
4. The dashboard displays a diff preview.
5. Click **ACCEPT & APPLY** to write the change to disk.

AI Fix supports:

- Blade files under `resources/views`
- React files under `resources/js`
- Vue files under `resources/js`

## PDF Reports

Click **EXPORT PDF** to generate a report containing:

- scan URL
- generation timestamp
- issue counts
- full violation list
- failing nodes
- source locations

## History Tab

The history tab stores and displays previous scans.

It includes:

- paginated scan list
- trend chart for recent scans
- per-scan issue details
- delete action
- compare action

## Scan Comparison

Compare two scans to see:

- **Fixed** issues that were present before and are gone now
- **New** issues that appeared in the later scan
- **Remaining** issues that exist in both scans

## Dashboard API Routes

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/lens-for-laravel/dashboard` | Render dashboard |
| `POST` | `/lens-for-laravel/crawl` | Discover internal URLs |
| `POST` | `/lens-for-laravel/scan` | Scan one URL |
| `POST` | `/lens-for-laravel/preview` | Screenshot a highlighted element |
| `POST` | `/lens-for-laravel/fix/suggest` | Request AI fix |
| `POST` | `/lens-for-laravel/fix/apply` | Apply AI fix |
| `POST` | `/lens-for-laravel/report/pdf` | Generate PDF |
| `GET` | `/lens-for-laravel/history` | List scans |
| `POST` | `/lens-for-laravel/history` | Store scan |
| `GET` | `/lens-for-laravel/history/trends` | Trend data |
| `GET` | `/lens-for-laravel/history/{id}` | Show scan |
| `DELETE` | `/lens-for-laravel/history/{id}` | Delete scan |
| `GET` | `/lens-for-laravel/history/{id}/compare/{compareId}` | Compare scans |

All routes are gated by `enabled_environments` and configured middleware.
