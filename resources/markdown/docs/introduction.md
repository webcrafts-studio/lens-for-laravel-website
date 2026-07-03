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

## What's New in v2.1.0

- **Interactive state scans** for menus, modals, validation states, tabs, dropdowns, and other UI that appears after interaction
- **Visual state recorder** for generating reusable interaction scripts in the dashboard
- **`stateLabel` metadata** on issues found during interactive state scans
- **State-aware history, PDF reports, and scan comparisons**
- **Baseline quality gate** for failing CI only when new violations appear
- **Dashboard localization** with English, Polish, Spanish, French, and German translations
- **Local HTTPS support** for self-signed development certificates through `LENS_FOR_LARAVEL_IGNORE_HTTPS_ERRORS`
- **Publishable translations** through the `lens-for-laravel-translations` tag

## Core Capabilities

- **WCAG A, AA, AAA, and best-practice scanning** powered by axe-core
- **JavaScript rendering** through Browsershot and Chromium
- **Blade, React, Vue, Livewire, and Inertia support**
- **Source locator** for Blade templates and frontend files under `resources/js`
- **Optional AI Fix Engine** using Gemini, OpenAI, or Anthropic on PHP 8.3+ and Laravel 12+
- **Four scan modes**: single URL, multiple URLs, whole-site crawl, and interactive states
- **Optional SPA crawling** for React/Vue/Inertia link discovery
- **Dashboard UI** with filtering, element preview, PDF export, AI fixes, history, and a state recorder
- **Artisan CLI** with level filtering, crawl mode, thresholds, baseline files, and CI integration

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

These versions apply to the core scanner. AI Fix additionally requires:

| AI Fix Dependency | Supported Versions |
|-------------------|-------------------|
| PHP | 8.3+ |
| Laravel | 12, 13 |
| Laravel AI SDK | `laravel/ai` 0.3+ installed separately |

On PHP 8.2 or Laravel 10/11, Lens keeps scanning, crawling, history, PDF, preview, source mapping, interactive states, and CLI support. Only AI Fix is unavailable, and the dashboard explains the limitation.

## Disclaimer

> **Automated tools catch only a portion of total WCAG accessibility issues.** Passing a Lens scan does not mean your application is fully accessible and does not guarantee compliance with the ADA, Section 508, EN 301 549, or the European Accessibility Act. Always complement automated scans with keyboard testing, screen reader testing, interaction-state testing, and manual review.
