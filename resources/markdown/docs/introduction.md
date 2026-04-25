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

## What's New in v2.0.0

- **React support** for source location and AI Fix
- **Vue support** for source location and AI Fix
- **Inertia-friendly discovery** for pages under `resources/js/Pages/**`
- **Livewire-friendly scan timing** through `LENS_FOR_LARAVEL_SCAN_WAIT_MS`
- **SPA crawler mode** with JavaScript-rendered link discovery
- **Scan history** with trend data and scan comparison
- **Source type labels** in the dashboard and scan payloads
- **Expanded security checks** for AI-generated fixes

## Core Capabilities

- **WCAG A, AA, AAA, and best-practice scanning** powered by axe-core
- **JavaScript rendering** through Browsershot and Chromium
- **Blade, React, Vue, Livewire, and Inertia support**
- **Source locator** for Blade templates and frontend files under `resources/js`
- **AI Fix Engine** using Gemini, OpenAI, or Anthropic
- **Three scan modes**: single URL, multiple URLs, and whole-site crawl
- **Optional SPA crawling** for React/Vue/Inertia link discovery
- **Dashboard UI** with filtering, element preview, PDF export, AI fixes, and history
- **Artisan CLI** with level filtering, crawl mode, thresholds, and CI integration

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
      +--> Dashboard / CLI / History
      |
      +--> AiFixer (optional)
                |
                +--> Gemini / OpenAI / Anthropic -> fixedCode + explanation
```

## Supported WCAG Levels

| Level | Coverage | Used For |
|-------|----------|----------|
| **A** | Minimum baseline | Critical blockers. Users with disabilities cannot access content. |
| **AA** | Standard target | Required by most legal frameworks and internal accessibility policies. |
| **AAA** | Enhanced | Best-practice improvements beyond most compliance requirements. |
| Best Practice | Axe extras | Non-WCAG rules that improve accessibility quality. |

Use `--a`, `--aa`, or `--all` in the CLI, or filter results in the dashboard.

## Version Compatibility

| Dependency | Supported Versions |
|------------|-------------------|
| PHP | 8.2, 8.3, 8.4 |
| Laravel | 10, 11, 12, 13 |
| Node.js | Recent LTS |
| Puppeteer | 21+ recommended |

## Disclaimer

> **Automated tools catch only a portion of total WCAG accessibility issues.** Passing a Lens scan does not mean your application is fully accessible and does not guarantee compliance with the ADA, Section 508, EN 301 549, or the European Accessibility Act. Always complement automated scans with keyboard testing, screen reader testing, interaction-state testing, and manual review.
