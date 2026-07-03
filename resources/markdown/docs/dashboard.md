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
2. Lens reads source context around the issue.
3. The configured AI provider returns `fixedCode` and an explanation.
4. The dashboard displays a diff preview.
5. Click **ACCEPT & APPLY** to write the change to disk.

Requesting a fix sends the issue details, failing DOM snippet, WCAG tags, and a limited source-code context to the configured Gemini, OpenAI, or Anthropic provider. It does not send the entire repository.

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

For interactive state scans, comparison includes the state label in the issue identity. The same selector in two different states can therefore be tracked separately.

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
