# Dashboard

The Lens for Laravel dashboard provides a full visual interface for running audits, exploring violations, requesting AI fixes, and exporting reports — all without touching the terminal.

## Accessing the Dashboard

Navigate to the dashboard URL in your browser:

```text
http://your-app.test/lens-for-laravel/dashboard
```

The prefix `lens-for-laravel` can be changed in the [configuration](/docs/configuration) via `route_prefix`.

> **Access control:** The dashboard is restricted to environments listed in `enabled_environments` (default: `local`). Requests from other environments receive a `403 Forbidden` response.

## Target Designation

The top panel of the dashboard is the **Target Designation** form — the entry point for all scans.

### Scan Modes

The form offers three scan modes, selectable via the mode tabs:

- **SINGLE_URL** — Audit one specific URL. Enter it in the input field and press **EXECUTE**.
- **WHOLE_WEBSITE** — Crawl the entire site starting from a base URL. The crawler discovers pages via sitemap seeding + BFS link traversal (up to `crawl_max_pages`).
- **MULTIPLE_URLS** — Scan a list of URLs. Enter each on a new line.

### Executing a Scan

1. Select your scan mode
2. Enter the target URL(s)
3. Click **EXECUTE**

The dashboard calls the `/lens-for-laravel/scan` API endpoint internally. For WHOLE_WEBSITE mode, it first calls `/lens-for-laravel/crawl` to discover pages, then scans each one.

## Diagnostic Report

After the scan completes, the **Diagnostic Report** panel displays the results.

### Summary Cards

Four cards at the top of the report show the violation count per WCAG level:

| Card | Content |
|------|---------|
| **A LEVEL** | Count of WCAG 2.x Level A violations (red highlight when > 0) |
| **AA LEVEL** | Count of WCAG 2.x Level AA violations |
| **AAA LEVEL** | Count of WCAG 2.x Level AAA violations |
| **OTHER** | Count of Axe-core best-practice violations |

A total `TOTAL_VIOLATIONS` count is shown alongside the **EXPORT PDF** button.

### Filtering Violations

Click any level card (A, AA, AAA) to filter the log to only violations of that level. Click again to clear the filter.

The filter label above the log changes to reflect the active filter (e.g., `FILTERED LOGS: WCAG2A`).

## Violation Cards

Each violation in the log is displayed as a card with:

- **Badge** — WCAG level tag (`[WCAG A]`, `[WCAG AA]`, etc.)
- **Rule ID** — The Axe-core rule name (e.g., `link-name`, `button-name`, `image-alt`)
- **Preview icon** — Highlight the failing element in a live screenshot
- **AI FIX button** — Request a Gemini-powered fix suggestion
- **VIEW DOCS link** — Opens the Deque/MDN documentation for the rule
- **Description** — Human-readable explanation of what the rule checks
- **Failing Node** — The HTML element that failed the test
- **SRC_LOC** — The detected Blade file and line number (click to open in your configured editor)
- **CSS_SELECTOR** — The full DOM path to the failing element (click to copy)

## AI Fix Workflow

1. Click **AI FIX** on a violation card
2. Lens calls the `/lens-for-laravel/fix/suggest` endpoint
3. Gemini AI analyses the failing node and Blade context (±20 lines)
4. A side panel shows the **original code** and **proposed fix** side by side with an explanation
5. Click **APPLY** to write the fix to disk, or **DISMISS** to discard it

See [AI Fix Engine](/docs/ai-fix-engine) for details on how the fix is generated.

## Exporting PDF Reports

Click **↓ EXPORT PDF** in the diagnostic report header to generate a downloadable PDF.

The report includes:

- Scan metadata (URL, timestamp, scan mode)
- Summary counts by WCAG level
- Full violation list with rule IDs, descriptions, failing nodes, and source locations

The PDF is generated server-side via the `/lens-for-laravel/report/pdf` endpoint.

## Dashboard API Routes

The dashboard communicates with these internal API endpoints:

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/lens-for-laravel/dashboard` | Renders the dashboard UI |
| `POST` | `/lens-for-laravel/crawl` | Discovers site pages (returns URL array) |
| `POST` | `/lens-for-laravel/scan` | Scans a single URL, returns violations |
| `POST` | `/lens-for-laravel/preview` | Screenshots a page highlighting an element |
| `POST` | `/lens-for-laravel/fix/suggest` | Requests a Gemini fix suggestion |
| `POST` | `/lens-for-laravel/fix/apply` | Writes the fix to a Blade file |
| `POST` | `/lens-for-laravel/report/pdf` | Generates and streams a PDF report |

All routes are protected by the `enabled_environments` gate and the `middleware` config option.
