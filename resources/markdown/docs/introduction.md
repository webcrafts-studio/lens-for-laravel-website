# Introduction

Lens for Laravel is a plug-and-play **WCAG accessibility auditor** for Laravel applications. It dynamically scans your pages for accessibility violations using [Axe-core 4.8.2](https://github.com/dequelabs/axe-core) via Spatie Browsershot, reverse-engineers failing CSS selectors back to their source Blade files, and provides an AI-powered fix engine backed by your choice of AI provider — Gemini, OpenAI, or Anthropic.

## What Is Lens for Laravel?

Most accessibility tools are standalone browser extensions or cloud services. Lens for Laravel is different — it lives inside your Laravel project as a Composer package and understands your application's structure.

When a violation is detected, Lens doesn't just show you the broken HTML element. It uses heuristics to trace that compiled output back to the exact `resources/views/**/*.blade.php` file and line number where the fix needs to happen.

## Core Capabilities

- **WCAG A, AA, and AAA compliance scanning** powered by the industry-standard Axe-core engine
- **Blade file locator** — maps compiled HTML violations to source Blade files using ID, class, name, and tag-based heuristics
- **AI Fix Engine** — your chosen AI provider (Gemini, OpenAI, or Anthropic) reads your Blade context and proposes a precise, minimal fix
- **Three scan modes** — single URL, multiple URLs, or a full site crawl via BFS + sitemap seeding
- **Artisan CLI** with level filtering, threshold quality gate, and CI/CD integration
- **Dashboard UI** — visual diagnostic report with per-level filtering and one-click AI fixes
- **PDF export** — generate a shareable compliance report

## How It Works

```text
Your Laravel App
      │
      ▼
 lens:audit or Dashboard
      │
      ├─► Spatie Browsershot (headless Chromium)
      │         │
      │         └─► Axe-core 4.8.2 injected via CDN
      │                   │
      │                   └─► Violation list (rule, impact, HTML snippet, CSS selector)
      │
      ├─► FileLocator (heuristics)
      │         │
      │         └─► resources/views/**/*.blade.php + line number
      │
      └─► AiFixer (optional)
                │
                └─► AI provider (Gemini / OpenAI / Anthropic) → fixedCode + explanation
```

## Supported WCAG Levels

| Level | Coverage | Used For |
|-------|----------|----------|
| **A** | Minimum baseline | Critical blockers. Users with disabilities cannot access content. |
| **AA** | Standard target | Required by most legal frameworks (ADA, EAA, Section 508). |
| **AAA** | Enhanced | Best-practice improvements beyond legal requirements. |
| Best Practice | Axe extras | Non-WCAG rules that improve overall accessibility quality. |

Use the `--a`, `--aa`, or `--all` flags to control which levels are reported.

## Version Compatibility

| Dependency | Supported Versions |
|------------|-------------------|
| PHP | 8.2, 8.3, 8.4 |
| Laravel | 10, 11, 12, 13 |
| Node.js | 18+ |
| Puppeteer | 21+ (global or local) |

## Disclaimer

> **Automated tools catch approximately 20–30% of total WCAG accessibility issues.** Passing a Lens for Laravel scan does not mean your application is fully accessible, nor does it guarantee compliance with the ADA, Section 508, or the European Accessibility Act. Always complement automated testing with manual keyboard testing, screen reader testing (NVDA, VoiceOver, JAWS), and cognitive walkthroughs.
