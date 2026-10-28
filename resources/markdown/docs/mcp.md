# Lens MCP

Lens MCP connects Codex, Claude Code, and other local MCP clients to the same scanner, source mapping, history, previews, and verification used by the Lens dashboard. Your coding assistant can inspect real findings, edit your application, and run a fresh audit without leaving its chat or CLI.

This integration uses the official `laravel/mcp` package. Your assistant supplies its own model, so Lens MCP does not require `laravel/ai`, an AI Fix provider, or provider credentials in Lens. The dashboard's optional [AI Fix Engine](/docs/ai-fix-engine) is a separate integration.

## Requirements

Install [Lens and Puppeteer](/docs/installation) in the application first. Lens MCP requires:

| Requirement | Supported versions |
|-------------|--------------------|
| PHP | 8.2+ |
| Laravel | 10.49+, 11, 12, or 13 |
| Optional SDK | `laravel/mcp` `>=0.5.3 <0.10` or `^1.0` |
| Browser | Puppeteer and Chromium available to the application's PHP process |
| Application | A configured `APP_URL`, an `APP_KEY`, and writable application storage |

Core Lens scanning still supports Laravel 10.0+. Applications below Laravel 10.49 can use the dashboard and `lens:audit` without MCP.

Install the SDK without pinning an old release:

```bash
composer require laravel/mcp --dev
```

Composer selects a version compatible with your PHP, Laravel, and other package constraints. Both the supported 0.x releases and stable 1.x releases work with Lens. An existing application constraint or locked dependency may still limit which version Composer can install.

## Connect Your Assistant

Run one configuration command for the client you use.

For Codex:

```bash
php artisan lens:mcp:install --client=codex
```

For Claude Code:

```bash
php artisan lens:mcp:install --client=claude
```

The installer adds a project-scoped `lens` server to `.codex/config.toml` or `.mcp.json`. It preserves unrelated configuration and refuses to replace a different existing Lens entry unless you explicitly pass `--force`. It also records MCP enablement for this checkout in application storage.

Reconnect your assistant or reload its MCP connections. The client starts `php artisan lens:mcp --enabled` automatically and communicates over local stdio. Keep your Laravel application running so Chromium can load the pages being audited.

The generated configuration contains the current PHP executable and absolute project paths. Run the installer again for another machine or checkout. Lens does not modify your global client configuration or start a public HTTP MCP endpoint.

The welcome command `lens:install` is independent of this connection setup. Existing installations can add MCP directly without running the welcome again.

## Your First Audit and Fix

For example, ask your assistant:

> Use Lens MCP to audit the homepage for WCAG 2.2 level A and AA issues. Inspect the mapped source, fix the relevant code, and verify the changes with a fresh Lens scan. Report what changed and any findings that remain.

The workflow is:

1. Call `lens_status` to inspect availability and configured limits.
2. Call `lens_scan` with the target and desired WCAG version and level.
3. Poll `lens_get_run` using the returned `run_id` until the run reaches a terminal state.
4. Read `lens_list_issues`, then request `lens_issue_context` for an individual `issue_ref`.
5. Review the mapping and use the assistant's normal file tools to edit the source. Call `lens_record_change` for each affected issue.
6. Call `lens_verify`, poll its new run, and inspect the comparison before reporting the result.

For the initial homepage scan, the tool arguments are:

```json
{
  "url": "/",
  "wcag": "2.2",
  "level": "aa"
}
```

`wcag` chooses the standard independently from `level`. The scan level `aa` includes A and AA; `all` includes AAA and best-practice findings too. WCAG 2.0 remains the default. The `level` filter on `lens_list_issues` selects one result bucket, so `aa` there returns only AA findings.

Source mapping is heuristic. A changed file or selector is not proof of a fix. Recorded edits remain pending verification and stay in violation counts until fresh axe-core results replace them. Partial or failed scans leave coverage incomplete and cannot confirm that every original finding was resolved.

## Available Tools

| Tool | Purpose |
|------|---------|
| `lens_status` | Inspect runtime, SDK availability, history, defaults, and limits. |
| `lens_scan` | Start a single-page, multi-URL, website, or interactive-state scan. |
| `lens_get_run` | Read scan status, progress, coverage, and verification comparison. |
| `lens_cancel_run` | Cancel a tracked scan. |
| `lens_list_issues` | Read paginated findings and filter by rule, impact, URL, state, file, or level. |
| `lens_issue_context` | Read the finding and bounded source context, with source hash and location. |
| `lens_record_change` | Record an edit made by the assistant's file tools as pending verification. |
| `lens_verify` | Re-scan with the original settings and compare the measured findings. |
| `lens_compare` | Compare two compatible runs and distinguish findings that need more verification. |
| `lens_history` | List saved dashboard/MCP scans or inspect one scan. |
| `lens_preview` | Capture the failing element, replaying interactive actions when needed. |
| `lens_export` | Export a completed or partial run to JSON or PDF. |
| `lens_baseline` | Compare a completed run with a baseline; saving requires write permission. |
| `lens_prepare_patch` | Prepare a bounded replacement when MCP writes are enabled. |
| `lens_apply_patch` | Apply a reviewed prepared patch when MCP writes are enabled. |

The two patch tools are hidden by default. Tool responses use server-issued run and issue references; pagination defaults to 25 items and allows up to 50 per page.

Clients can also discover the `lens://capabilities` resource, the `lens://runs/{run_id}/summary` resource template, and the `lens_accessibility_fix` prompt containing the recommended workflow. How a client presents these capabilities depends on that client.

## Long Scans

`lens_scan` and `lens_verify` use asynchronous execution by default. A worker performs the browser work while the MCP connection remains available for progress checks and cancellation. No Laravel queue worker is required for this execution mode.

Run states are `queued`, `running`, `completed`, `partial`, `failed`, and `cancelled`. `partial` means some requested pages were not scanned successfully. Keep the client connected during an asynchronous scan; closing the MCP server cancels workers owned by that connection. Stored results remain available until retention cleanup.

Use the CLI when you want to manage a tracked scan directly:

```bash
# Create a queued website scan and read its returned run_id
php artisan lens:mcp:scan / --crawl --wcag=2.2 --level=aa --queue

# Inspect or execute that queued run
php artisan lens:mcp:runs status <run-id>
php artisan lens:mcp:runs work <run-id>

# Inspect other runs, cancel work, or remove expired data
php artisan lens:mcp:runs list
php artisan lens:mcp:runs cancel <run-id>
php artisan lens:mcp:runs prune
```

Without `--queue`, `lens:mcp:scan` performs the scan in the current process and prints a JSON summary. It also accepts repeated `--urls=...`, `--script=...` containing the shared interaction grammar, and `--as-user=...`. Interactive states require one URL and cannot be combined with crawl or multi-URL mode.

Run `lens:mcp:runs work` in another terminal if you need to inspect or cancel it concurrently. The hidden `lens:mcp:work` command is the worker entry point used internally; use the public `runs work` action for manual execution. For threshold-based CI checks, use the existing `lens:audit` command and its exit-code/baseline options.

## History, Dashboard, and Reports

Run the application's pending migrations to enable shared scan history, including the v3.6 nullable `run_id` column:

```bash
php artisan migrate
```

MCP scans save history by default when the history tables are available. If history cannot be saved, the scan remains usable and its summary includes a warning. `lens_history` requires the history tables; the encrypted run store works independently.

The dashboard and MCP use the same issue DTOs, source mapping, WCAG controls, authenticated-scan resolver, previews, reports, and baseline behavior. When MCP is available, the dashboard can synchronize externally recorded source changes and mark matching current findings as pending verification.

For authenticated pages, use `as_user_id` with the existing [authenticated-scan configuration](/docs/scanning-modes). Only an existing user ID is accepted. Cookie creation and restoration happen server-side, and cookies are not stored in history.

## Configuration and Write Access

The `mcp` configuration block is optional in an older published Lens config. Built-in defaults are merged automatically. See [Configuration](/docs/configuration) for every setting and limit.

The project installer enables MCP for the current checkout. You can enable it explicitly with:

```text
LENS_FOR_LARAVEL_MCP_ENABLED=true
```

An explicit `false` disables marker-based enablement in the dashboard and ordinary run commands. The `--enabled` server flag is a per-process override, and generated client configuration uses that flag. To stop a configured assistant connection, disable or remove its Lens server entry and disconnect it. The environment must also be in `enabled_environments`, which defaults to `local`.

The default workflow uses the assistant's native file editor and `lens_record_change`. To let Lens apply prepared patches and save baselines through MCP, opt in:

```text
LENS_FOR_LARAVEL_MCP_ALLOW_WRITES=true
```

Reconnect the client after changing configuration. If configuration is cached, run `php artisan config:clear` first.

Prepared patches contain the original source hash and exact occurrence. Lens rejects stale files, unsupported paths, expired patches, and disallowed code. Patches expire after ten minutes. Applying one marks the issue pending verification. `allow_writes` controls Lens patch/baseline writes; your assistant's native file permissions are controlled by the assistant itself.

## Privacy and Limits

MCP uses local stdio, but the connected assistant receives the requested issue metadata, DOM snippets, bounded source context, and screenshots. A cloud-backed assistant may send that context to its own model provider according to its settings. Lens MCP does not call the dashboard AI Fix provider.

Source context is limited to 6000 bytes. Run snapshots are encrypted using the application key and stored under private application storage. Defaults allow two active runs, 200 stored runs, 24-hour retention, a 900-second run deadline, and a 120-second browser timeout. The default crawl limit is 50 pages. MCP replies are bounded, so request a smaller page when a result is too large.

Targets are limited to the origin of `APP_URL`, including scheme and port. Add another explicitly trusted origin to `mcp.allowed_origins` only when it is part of your local application setup. Redirects and partial coverage do not establish successful verification.

Page HTML and source fragments are untrusted content, not instructions for the assistant. Automated checks still require complementary manual accessibility testing.

## Troubleshooting

```bash
php artisan lens:mcp:runs doctor
```

The diagnostic output reports MCP availability, runtime versions, AI Fix availability, history support, defaults, and limits. A missing `laravel/ai` only affects dashboard AI Fix.

- **SDK unavailable:** install `laravel/mcp` on Laravel 10.49+ and let Composer resolve its dependencies.
- **Connection does not appear:** run the correct client installer in this project and reconnect the client.
- **Wrong PHP or project path:** rerun the installer in the current checkout. Use `--force` only when replacing an existing Lens entry intentionally.
- **MCP disabled:** inspect the explicit environment setting, installation marker, and allowed environments.
- **Browser cannot load a page:** check `APP_URL`, the running application, Puppeteer/Chromium, and local HTTPS configuration.
- **History unavailable:** run the pending migrations or continue with run snapshots and `save_history: false`.
- **Source changed:** obtain a fresh scan and context before preparing another edit.
- **Worker failed or deadline exceeded:** inspect the run error, fix the environment or reduce the scan scope, then start a new run. A failed scan is not a clean audit.
