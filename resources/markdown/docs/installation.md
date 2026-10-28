# Installation

## Requirements

Before installing Lens for Laravel, make sure the host application has:

- **PHP** 8.2 or higher
- **Laravel** 10, 11, 12, or 13
- **Node.js** recent LTS
- **Puppeteer** for headless Chromium
- **Chromium** available through Puppeteer or your deployment environment

Lens uses Spatie Browsershot to render pages and run axe-core against the browser DOM.

## Install Puppeteer

Install Puppeteer as a local dev dependency in the Laravel app:

```bash
npm install puppeteer --save-dev
```

You can also install it globally if your environment is already configured that way:

```bash
npm install -g puppeteer
```

> On first install, Puppeteer downloads a compatible Chromium build. This is expected.

## Install the Package

```bash
composer require webcrafts-studio/lens-for-laravel --dev
```

Lens is a developer tool and should normally be installed as a dev dependency.

The command above installs every core scanning feature on PHP 8.2+ and Laravel 10-13. It intentionally does not install an AI or MCP SDK.

## Welcome and Setup Check

After installing Lens and Puppeteer, run:

```bash
php artisan lens:install
```

The first run shows the dashboard URL, the optional AI Fix and MCP integrations, documentation links, and a short GitHub star message. Installed integrations use neutral statuses such as `SDK installed` or `Client configured`.

This command records its first run in private application storage. It does not install SDKs, publish files, modify `.env`, configure an MCP client, or run migrations. Repeating it succeeds and shows the dashboard URL without repeating the welcome. `--no-interaction`, `--no-ansi`, and `--quiet` are supported.

Package discovery and Composer updates do not show this welcome automatically. Existing applications can continue using Lens without running the command. Add optional integrations below whenever you need them; there is no need to repeat `lens:install` after each SDK installation.

## Optional AI Fix Installation

AI Fix has a narrower compatibility range than the core scanner:

- PHP 8.3 or newer
- Laravel 12 or newer
- optional `laravel/ai` package

Install it separately on a supported application:

```bash
composer require laravel/ai --dev
```

Lens supports `laravel/ai` `>=0.3.2 <0.12` and stable `^1.0`. Composer selects a compatible version for the application's PHP, Laravel, and other dependency constraints. Lens does not force Laravel 13 applications to install an older 0.x SDK. Existing root constraints and the lock file still apply.

Laravel 10/11 and PHP 8.2 applications continue to receive all non-AI Lens features. The dashboard hides AI Fix actions and displays the exact reason they are unavailable.

## Optional MCP Installation

To work with Lens from Codex or Claude Code, install the official optional SDK:

```bash
composer require laravel/mcp --dev
```

Lens MCP requires PHP 8.2+ and Laravel 10.49+, 11, 12, or 13. Supported SDK versions are `>=0.5.3 <0.10` or stable `^1.0`; Composer chooses a compatible release.

Configure the client you use:

```bash
php artisan lens:mcp:install --client=codex
```

Use `--client=claude` for Claude Code. Reconnect your assistant to load the project-scoped server configuration. The client starts Lens MCP automatically. Your assistant uses its own model, so this integration does not require `laravel/ai` or an AI Fix provider.

See [Lens MCP](/docs/mcp) for the tools, long scans, verification workflow, and write permissions.

## Optional Scan History

Run the application's pending migrations to install the Lens history tables and metadata, including v3.6 MCP run identity. Scanning works without history:

```bash
php artisan migrate
```

## Optional Configuration

```bash
php artisan vendor:publish --tag="lens-for-laravel-config"
```

This creates:

```text
config/lens-for-laravel.php
```

Optionally publish package views:

```bash
php artisan vendor:publish --tag="lens-for-laravel-views"
```

Optionally publish package translations:

```bash
php artisan vendor:publish --tag="lens-for-laravel-translations"
```

## Environment Variables

Add only the options you need:

```text
LENS_FOR_LARAVEL_EDITOR=vscode
LENS_FOR_LARAVEL_LOCALE=en
LENS_FOR_LARAVEL_FALLBACK_LOCALE=en
LENS_FOR_LARAVEL_CRAWL_MAX_PAGES=50
LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=false
LENS_FOR_LARAVEL_SCAN_WAIT_MS=0
LENS_FOR_LARAVEL_WCAG_VERSION=2.0
LENS_FOR_LARAVEL_BASELINE_PATH=storage/app/lens-for-laravel/baseline.json
LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS=false
LENS_FOR_LARAVEL_AI_ENABLED=true
LENS_FOR_LARAVEL_AI_PROVIDER=gemini
LENS_FOR_LARAVEL_AI_MODEL=
LENS_FOR_LARAVEL_MCP_ENABLED=
LENS_FOR_LARAVEL_MCP_ALLOW_WRITES=false
LENS_FOR_LARAVEL_AI_OLLAMA_MODEL=
LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT=120
```

| Variable | Default | Description |
|----------|---------|-------------|
| `LENS_FOR_LARAVEL_EDITOR` | `vscode` | IDE used when opening source files from the dashboard. |
| `LENS_FOR_LARAVEL_LOCALE` | app locale | Default dashboard locale. |
| `LENS_FOR_LARAVEL_FALLBACK_LOCALE` | `en` | Fallback locale used when a translation key is missing. |
| `LENS_FOR_LARAVEL_CRAWL_MAX_PAGES` | `50` | Maximum pages discovered in whole-site mode. |
| `LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT` | `false` | Render JavaScript while crawling SPA/Inertia links. |
| `LENS_FOR_LARAVEL_SCAN_WAIT_MS` | `0` | Extra delay after network idle before axe-core runs. |
| `LENS_FOR_LARAVEL_WCAG_VERSION` | `2.0` | Default WCAG standard for dashboard and CLI scans (`2.0`, `2.1`, or `2.2`). |
| `LENS_FOR_LARAVEL_BASELINE_PATH` | `storage/app/lens-for-laravel/baseline.json` | Default file used by the baseline quality gate. |
| `LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS` | `false` | Ignore self-signed HTTPS certificate errors during local scans. |
| `LENS_FOR_LARAVEL_AI_ENABLED` | `true` | Explicitly enable or disable the optional AI Fix integration. |
| `LENS_FOR_LARAVEL_AI_PROVIDER` | `gemini` | AI provider: `gemini`, `openai`, `anthropic`, `openrouter`, `xai`, `deepseek`, `mistral`, or `ollama`. |
| `LENS_FOR_LARAVEL_AI_MODEL` | SDK default | Optional explicit model override for any provider. |
| `LENS_FOR_LARAVEL_MCP_ENABLED` | installation marker | Explicitly enable/disable MCP, or leave unset to use project installation state. |
| `LENS_FOR_LARAVEL_MCP_ALLOW_WRITES` | `false` | Allow prepared patch application and baseline saves through MCP. |
| `LENS_FOR_LARAVEL_AI_OLLAMA_MODEL` | SDK default | Exact locally installed Ollama model tag used when the provider is `ollama`. |
| `LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT` | `120` | Ollama request timeout in seconds for slower local generation. |

## AI Provider Keys

The [AI Fix Engine](/docs/ai-fix-engine) documents every supported provider and its credentials.

AI Fix is optional. After installing `laravel/ai` on a supported runtime, configure a provider only if you want generated fixes:

```text
LENS_FOR_LARAVEL_AI_PROVIDER=gemini
GEMINI_API_KEY=your-key

# or
LENS_FOR_LARAVEL_AI_PROVIDER=openai
OPENAI_API_KEY=your-key

# or
LENS_FOR_LARAVEL_AI_PROVIDER=anthropic
ANTHROPIC_API_KEY=your-key
```

For local generation, install [Ollama](https://ollama.com/download), pull a code-capable model, and point Lens at it:

```bash
ollama pull qwen2.5-coder:7b
```

```text
LENS_FOR_LARAVEL_AI_PROVIDER=ollama
LENS_FOR_LARAVEL_AI_OLLAMA_MODEL=qwen2.5-coder:7b
LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT=120
OLLAMA_URL=http://127.0.0.1:11434
```

The URL is optional when Ollama uses its default local address. With `laravel/ai` 0.3.x, use `OLLAMA_BASE_URL` instead of `OLLAMA_URL` when overriding that address. No API key is needed for the default local Ollama server.

Set `LENS_FOR_LARAVEL_AI_ENABLED=false` to disable AI Fix explicitly while keeping all scanning features enabled.

## Verify Installation

Open the dashboard:

```text
http://your-app.test/lens-for-laravel/dashboard
```

Or run a CLI scan:

```bash
php artisan lens:audit http://your-app.test
```

## CI and Staging

Lens is enabled only in `local` by default. To run it in CI, add the environment explicitly:

```php
'enabled_environments' => ['local', 'testing'],
```

If you enable Lens on staging, protect the routes:

```php
'middleware' => ['web', 'auth'],
```

> Do not expose Lens publicly in production. It launches headless browsers and exposes internal source structure.
