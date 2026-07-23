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
- **Interactive States** scans

Before scanning, choose **WCAG 2.0**, **WCAG 2.1**, or **WCAG 2.2**. WCAG 2.0 is selected by default to preserve the behavior of existing workflows. The newer choices are cumulative: a 2.2 scan also runs the applicable axe-core rules for 2.0 and 2.1.

For whole-site scans, the dashboard first calls the crawl endpoint, then scans each discovered URL.

For interactive state scans, the dashboard runs a plain-text interaction script before axe-core scans each named state.

## Interactive States Tab

Interactive states help test UI that does not exist on initial page load:

- opened mobile navigation
- visible form validation messages
- open modal dialogs
- expanded dropdowns
- selected tabs
- Livewire, React, Vue, or Inertia states after interaction

The dashboard accepts an interaction script:

```text
state: Navigation open
click: [data-menu-button]

state: Form validation
type: input[name="email"] => invalid@example.test
click: button[type="submit"]
wait: 300
```

Supported actions:

- `click`
- `type`
- `select`
- `check`
- `uncheck`
- `wait`

Each `state:` label is attached to the issues found after its actions run.

## State Recorder

The recorder opens the target page in a controlled Lens view and helps generate interaction scripts visually.

Use it when writing selectors manually would be slow:

1. Enter a same-origin URL in the dashboard.
2. Switch to **Interactive States**.
3. Click **Record**.
4. Interact with the target page.
5. Create named states for the UI you want to scan.
6. Send the generated script back to the dashboard.

The script remains editable, so you can clean up selectors, rename states, or add waits before scanning.

## Diagnostic Report

The report includes:

- total violation count
- selected WCAG standard version
- WCAG A, AA, AAA, and other counts
- issue impact and rule ID
- failing DOM node
- source location
- source type badge: `blade`, `react`, or `vue`
- interactive state label when available
- CSS selector
- documentation link
- preview button
- AI Fix button when a supported source file is found
- Fix All A and Fix All AA actions when the selected level contains located, reviewable issues

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

AI Fix actions are shown only when the host application uses PHP 8.3+, Laravel 12+, has the optional `laravel/ai` package installed, and has not disabled AI Fix in configuration. On older supported applications, the dashboard shows an availability notice while every scanning feature remains usable.

1. Click **AI FIX** on a located issue.
2. Lens extracts the smallest relevant element or component around the issue.
3. The configured AI provider returns a minimal replacement and an explanation.
4. The dashboard displays a diff preview.
5. Accept the suggestion as generated or click **EDIT** to change it in the modal. The editor provides line numbers, Tab and Shift+Tab indentation, a live diff, reset-to-AI, and Ctrl/Cmd+Enter apply.
6. Click **ACCEPT & APPLY** to write the reviewed replacement to disk, or reject it without changing a file.
7. The issue card is immediately marked **AI Fix applied — pending re-scan**.

## Fix All A and AA in v3.1

After a scan, **Fix All A** and **Fix All AA** appear when that level contains issues with located, supported source files.

Fix All is a review workflow, not blind bulk writing:

1. The modal opens immediately with every eligible issue in a numbered queue.
2. Lens generates up to three suggestions concurrently. Their starts are slightly staggered so applications using SQLite-backed cache stores do not contend on the local rate limiter.
3. Each queue position shows `queued`, `generating`, `ready`, `failed`, `applied`, or `rejected` state.
4. Ready proposals can be reviewed and edited while later positions continue loading.
5. Previous/next controls and numbered status buttons preserve the edited code for every item.
6. A failed item can be retried without restarting successful suggestions.
7. Accepting or rejecting one item does not close or block the rest of the queue.
8. Closing the modal aborts outstanding queue requests.

Only WCAG A and AA have Fix All actions. Issues without a supported source location are not added to the queue and remain available for manual investigation.

Requesting a fix sends the issue details, failing DOM snippet, WCAG tags, and a limited source-code context to the configured Gemini, OpenAI, or Anthropic provider. It does not send the entire repository.

Since v3.0, Lens uses the provider's default model from `laravel/ai`; there is no model selector. Truncated or malformed structured output is retried once. If the second attempt also fails, the modal explains that no file was changed instead of exposing the provider's raw JSON error.

The pending marker is intentionally not the same as a verified fix. The issue remains in totals and filters because those numbers describe the last axe-core scan. Closing the modal keeps the marker visible. Running a new scan replaces the old state with fresh results.

In v3.1, every browser scan uses a unique cache-busting query parameter and no-cache headers while the issue still retains the originally requested URL. The dashboard assigns stable identities to the new result objects and cancels an older AI request when another issue or queue supersedes it. This prevents stale markup or a late response from keeping the modal bound to a previous issue.

AI Fix supports:

- Blade files under `resources/views`
- React files under `resources/js`
- Vue files under `resources/js`

## PDF Reports

Click **EXPORT PDF** to generate a report containing:

- scan URL
- selected WCAG standard version
- generation timestamp
- issue counts
- full violation list
- failing nodes
- source locations
- interactive state labels when available

## History Tab

The history tab stores and displays previous scans.

It includes:

- paginated scan list
- trend chart for recent scans
- per-scan issue details
- WCAG standard used by each scan
- delete action
- compare action

## Scan Comparison

Compare two scans to see:

- **Fixed** issues that were present before and are gone now
- **New** issues that appeared in the later scan
- **Remaining** issues that exist in both scans

Comparison identifies an issue by its axe rule, normalized URL path and query, interactive state label, and selector. The same `.btn` selector on `/account` and `/checkout` is therefore tracked as two separate issues. Scheme and host are ignored, so equivalent local and CI addresses can still be compared.

## Language Switcher

The dashboard includes a language switcher when multiple supported locales are configured.

Bundled locales:

- English
- Polish
- Spanish
- French
- German

The selected locale is stored in the session. Defaults come from:

```text
LENS_FOR_LARAVEL_LOCALE=en
LENS_FOR_LARAVEL_FALLBACK_LOCALE=en
```

Since v3.0, the bundled catalogs cover every package-owned label and message in the scanner, history, URL-aware comparisons, AI Fix and preview modals, interactive-state recorder, charts, PDF reports, and package-generated browser and route errors. v3.1 extends the same five-language contract to the editor and Fix All queue. PDF exports follow the locale selected in the dashboard session.

Descriptions of accessibility rules come from axe-core, while some request-validation messages can come from Laravel itself. Those upstream messages are not rewritten by Lens and may use the language configured by the supplying library or host application.

## Dashboard API Routes

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/lens-for-laravel/dashboard` | Render dashboard |
| `GET` | `/lens-for-laravel/states/recorder` | Render state recorder |
| `POST` | `/lens-for-laravel/crawl` | Discover internal URLs |
| `POST` | `/lens-for-laravel/scan` | Scan one URL |
| `POST` | `/lens-for-laravel/scan/states` | Scan interactive states |
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
