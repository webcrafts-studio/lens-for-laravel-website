# Introduction

Lens for Laravel is a local-first **WCAG accessibility auditor** for Laravel applications. It scans your rendered application with [axe-core](https://github.com/dequelabs/axe-core), runs JavaScript through Spatie Browsershot, maps violations back to source files, and can generate AI-assisted fixes for Blade, React, and Vue code.

## What Is Lens for Laravel?

Most accessibility tools are browser extensions, SaaS dashboards, or generic CI scanners. Lens lives inside your Laravel project as a Composer package and understands common Laravel frontend structures.

When axe-core detects a violation, Lens shows the failing DOM element and attempts to map it back to the source that produced it:

- `resources/views/**/*.blade.php`
- `resources/js/**/*.js`
- `resources/js/**/*.jsx`
- `resources/js/**/*.ts`
- `resources/js/**/*.tsx`
- `resources/js/**/*.vue`

Located issues include a file path, line number, and `sourceType`: `blade`, `react`, or `vue`.

## What's New in v3.4

v3.4 is the current development line. It adds authenticated scans for pages behind login plus four additional AI Fix providers (OpenRouter, xAI, DeepSeek, Mistral). The v3.3 Ollama support, v3.2 structural source mapping, v3.1 review workflows, and v3.0 compatibility foundation remain unchanged.

New in v3.4:

- **Authenticated scans** for pages behind login via `--as-user` or the dashboard user ID field
- **More AI Fix providers** with implicit SDK-default models: OpenRouter, xAI, DeepSeek, and Mistral

## What's New in v3.3

v3.3 is the current development line. Its only new feature is local AI Fix model support through Ollama. The v3.2 structural source mapping, v3.1 review workflows, and v3.0 compatibility foundation remain unchanged.

New in v3.3:

- **Local AI Fix inference** through the native Ollama provider in `laravel/ai`
- **Explicit local model selection** with `LENS_FOR_LARAVEL_AI_OLLAMA_MODEL`
- **Local-first privacy** when Ollama uses its default localhost endpoint, while retaining the same bounded source context, review diff, safety checks, and pending re-scan state

Added in v3.2:

- **Named Blade route mapping** resolves a rendered same-host URL back to helpers such as `route('home')`
- **Correct target boundaries** prevent nested child attributes and classes from being mistaken for attributes of the failing element
- **Nested element matching** compares bounded multiline Blade, React, and Vue source blocks with recognizable descendant markup
- **Selector context matching** uses nearby ancestor classes and IDs to distinguish similar dynamic elements
- **Mixed-frontend precedence** preserves stronger React/Vue structural matches ahead of weaker Blade fallbacks

Added in v3.1:

- **Editable AI Fix proposals** directly in the modal, with line numbers, Tab and Shift+Tab indentation, live diff updates, keyboard apply, and reset to the original AI suggestion
- **Fix All A and Fix All AA review queues** that start immediately and generate up to three suggestions concurrently
- **Progressive review** so ready fixes can be inspected, edited, accepted, rejected, or retried while later queue items still show loaders
- **Fresh re-scan navigation** with cache-busted browser URLs, stable issue identities, and cancellation of superseded AI requests
- **More precise source mapping** for repeated elements by preferring rendered source filenames and using numeric `:nth-child(...)` hints

The v3.0 foundation includes:

- **Selectable WCAG 2.0, 2.1, and 2.2 standards** in the dashboard and CLI
- **WCAG 2.0 as the backward-compatible default** for existing commands and CI workflows
- **WCAG version metadata** in scan history, comparisons, baselines, and PDF reports
- **URL-aware history comparisons** that distinguish identical selectors on different pages
- **Interactive-state scripts in the CLI** through `--states=path`
- **Consistent local HTTPS handling** for scans, crawler requests, browser-rendered discovery, and previews
- **Broader core compatibility** across PHP 8.2+ and Laravel 10-13
- **Optional, compatibility-gated AI Fix** on PHP 8.3+, Laravel 12+, and the separately installed `laravel/ai` SDK
- **Reliable minimal AI replacements** using semantic source context, bounded Gemini thinking, and one controlled structured-output retry
- **Honest post-fix feedback** that marks applied AI changes as pending until a new axe-core scan verifies them
- **Complete package-owned interface catalogs** in English, Polish, Spanish, French, and German, including history, comparisons, modals, PDF reports, and error feedback

The Version 3 upgrade page covers the v3.0 foundation and incremental v3.1/v3.2/v3.3/v3.4 changes. The v2.0 and v2.1 upgrade pages remain available as historical documentation for projects upgrading from older releases.

## Core Capabilities

- **WCAG A, AA, AAA, and best-practice scanning** powered by axe-core
- **Selectable WCAG 2.0, 2.1, and 2.2 rule sets** in the dashboard and CLI, with 2.0 as the backward-compatible default
- **JavaScript rendering** through Browsershot and Chromium
- **Blade, React, Vue, Livewire, and Inertia support**
- **Source locator** for Blade templates and frontend files under `resources/js`
- **Optional AI Fix Engine** using Gemini, OpenAI, Anthropic, OpenRouter, xAI, DeepSeek, Mistral, or a local Ollama model on PHP 8.3+ and Laravel 12+
- **Four scan modes**: single URL, multiple URLs, whole-site crawl, and interactive states
- **Optional SPA crawling** for React/Vue/Inertia link discovery
- **Dashboard UI** with filtering, element preview, PDF export, editable AI fixes, progressive Fix All A/AA queues, history, and a state recorder
- **Artisan CLI** with interactive states, level filtering, crawl mode, thresholds, baseline files, and CI integration

## How It Works

```text
Your Laravel App
      |
      v
 lens:audit or Dashboard
      |
      +--> Spatie Browsershot (headless Chromium)
      |         |
      |         +--> axe-core injected into the rendered DOM
      |                   |
      |                   +--> violations: rule, impact, HTML, selector, URL
      |
      +--> FileLocator (heuristics)
      |         |
      |         +--> Blade / React / Vue file + line + sourceType
      |
      +--> Dashboard / CLI / History / Baseline
      |
      +--> AiFixer (optional)
                |
                +--> Ollama / Gemini / OpenAI / Anthropic / OpenRouter / xAI / DeepSeek / Mistral -> fixedCode + explanation
```

## Supported WCAG Levels

| Level | Coverage | Used For |
|-------|----------|----------|
| **A** | Minimum baseline | Critical blockers. Users with disabilities cannot access content. |
| **AA** | Standard target | Required by most legal frameworks and internal accessibility policies. |
| **AAA** | Enhanced | Best-practice improvements beyond most compliance requirements. |
| Best Practice | Axe extras | Non-WCAG rules that improve accessibility quality. |

Use `--a`, `--aa`, or `--all` in the CLI, or filter results in the dashboard.

The standard version is a separate choice from the conformance level. WCAG 2.1 adds its axe-core rules to the 2.0 set, and WCAG 2.2 adds its rules to the cumulative 2.0 + 2.1 set. Select the version in the dashboard or use `--wcag=2.0`, `--wcag=2.1`, or `--wcag=2.2` in the CLI.

## Version Compatibility

| Dependency | Supported Versions |
|------------|-------------------|
| PHP | 8.2+ |
| Laravel | 10, 11, 12, 13 |
| Node.js | Recent LTS |
| Puppeteer | 21+ recommended |

These versions apply to the core scanner. AI Fix additionally requires:

| AI Fix Dependency | Supported Versions |
|-------------------|-------------------|
| PHP | 8.3+ |
| Laravel | 12, 13 |
| Laravel AI SDK | `laravel/ai` 0.3.2 or newer, installed separately |

On PHP 8.2 or Laravel 10/11, Lens keeps scanning, crawling, history, PDF, preview, source mapping, interactive states, and CLI support. Only AI Fix is unavailable, and the dashboard explains the limitation.

## Disclaimer

> **axe-core automates many high-confidence accessibility checks, but neither axe-core nor Lens can determine full WCAG conformance.** Passing a Lens scan does not mean your application is fully accessible and does not guarantee compliance with the ADA, Section 508, EN 301 549, or the European Accessibility Act. Always complement automated scans with keyboard testing, screen reader testing, interaction-state testing, and manual review.
