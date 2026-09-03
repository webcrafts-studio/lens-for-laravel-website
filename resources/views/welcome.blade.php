<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lens for Laravel — WCAG Accessibility Auditor</title>
    <meta name="description"
        content="Local-first WCAG accessibility auditor for Laravel. Maps violations to Blade, Livewire, React, and Vue source files, scans interactive states, and supports CI baselines.">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" data-theme-favicon>

    {{-- Anti-FOUC: apply saved theme before first paint --}}
    <script>
        (function() {
            var saved = localStorage.getItem('lens-theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }

            updateFavicon(document.documentElement.classList.contains('dark'));
        })();

        function toggleTheme() {
            var isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('lens-theme', isDark ? 'dark' : 'light');
            updateThemeToggle(isDark);
            updateFavicon(isDark);
        }

        function updateFavicon(isDark) {
            var favicon = document.querySelector('[data-theme-favicon]');

            if (!favicon) {
                return;
            }

            favicon.href = isDark ? @js(asset('favicon-dark.svg')) : @js(asset('favicon.svg'));
        }

        function updateThemeToggle(isDark) {
            var toggle = document.querySelector('[data-theme-toggle]');

            if (!toggle) {
                return;
            }

            var label = isDark ? 'Switch to light mode' : 'Switch to dark mode';
            toggle.setAttribute('aria-label', label);
            toggle.setAttribute('title', label);
            toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        }

        function copyCmd(btn, text) {
            var finish = function() {
                var label = btn.querySelector('.copy-label');
                if (label) {
                    label.textContent = 'COPIED';
                    var icon = btn.querySelector('svg');
                    if (icon) {
                        icon.style.opacity = '0.3';
                    }
                    setTimeout(function() {
                        label.textContent = 'COPY';
                        if (icon) {
                            icon.style.opacity = '';
                        }
                    }, 1500);
                } else {
                    btn.textContent = 'COPIED';
                    setTimeout(function() {
                        btn.textContent = 'COPY';
                    }, 1500);
                }
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(finish).catch(function() {
                    fallbackCopy(text, finish);
                });
            } else {
                fallbackCopy(text, finish);
            }
        }

        function fallbackCopy(text, callback) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;top:0;left:0;opacity:0;';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try {
                document.execCommand('copy');
            } catch (e) {}
            document.body.removeChild(ta);
            if (callback) {
                callback();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateThemeToggle(document.documentElement.classList.contains('dark'));
        });
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600,700,800" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

{{-- Default = light (white bg, black text). Dark mode via .dark class on <html>. --}}

<body class="bg-page text-body font-mono antialiased">

    <a href="#main-content" class="skip-link">Skip to main content</a>

    {{-- ====================================================== --}}
    {{-- NAVIGATION                                              --}}
    {{-- ====================================================== --}}
    <nav aria-label="Primary navigation"
        class="fixed top-0 left-0 right-0 z-50 bg-page border-b-2 border-divider">
        <div class="max-w-7xl mx-auto px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="shrink-0 whitespace-nowrap text-content font-black text-lg tracking-[0.15em] uppercase">LENS FOR</span>
                <span class="shrink-0 whitespace-nowrap text-accent font-black text-lg tracking-[0.15em] uppercase">LARAVEL</span>
                <span
                    class="ml-2 hidden sm:inline text-subtle text-xs font-mono border border-control px-1.5 py-0.5 leading-none">v3.3</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="#features"
                    class="hidden md:block text-muted hover:text-content text-xs font-mono tracking-widest transition-colors uppercase">Features</a>
                <a href="#cli"
                    class="hidden md:block text-muted hover:text-content text-xs font-mono tracking-widest transition-colors uppercase">CLI</a>

                {{-- Theme toggle --}}
                <button type="button" data-theme-toggle onclick="toggleTheme()" aria-label="Switch to dark mode"
                    aria-pressed="false"
                    class="w-9 h-9 flex items-center justify-center border-2 border-control hover:border-content text-muted hover:text-content transition-colors text-base cursor-pointer">
                    <span class="dark:hidden" aria-hidden="true">☾</span>
                    <span class="hidden dark:inline" aria-hidden="true">☀</span>
                </button>

                <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                    class="hidden sm:block text-content font-mono text-xs border-2 border-control hover:border-content px-3 md:px-4 py-2 hover:bg-content hover:text-page transition-colors uppercase tracking-widest">
                    GitHub →
                </a>
            </div>
        </div>
    </nav>

    {{-- ====================================================== --}}
    {{-- HERO                                                    --}}
    {{-- ====================================================== --}}
    <main id="main-content" tabindex="-1">
    <section class="bg-page min-h-screen flex flex-col pt-14 relative overflow-hidden">

        {{-- Grid backgrounds (light vs dark) --}}
        <div class="dark:hidden absolute inset-0 opacity-[0.05] pointer-events-none"
            style="background-image: linear-gradient(#000 1px, transparent 1px), linear-gradient(90deg, #000 1px, transparent 1px); background-size: 48px 48px;">
        </div>
        <div class="hidden dark:block absolute inset-0 opacity-[0.03] pointer-events-none"
            style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 48px 48px;">
        </div>

        <div class="flex-1 flex flex-col items-center justify-center p-6 md:p-12">
            {{-- Corner bracket frame --}}
            <div
                class="relative border border-divider px-8 py-16 md:px-20 md:py-20 max-w-5xl w-full text-center">
            <div
                class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-accent -translate-x-px -translate-y-px">
            </div>
            <div
                class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-accent translate-x-px -translate-y-px">
            </div>
            <div
                class="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-accent -translate-x-px translate-y-px">
            </div>
            <div
                class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-accent translate-x-px translate-y-px">
            </div>

            <div
                class="text-accent text-xs font-mono font-bold tracking-normal md:tracking-[0.3em] mb-10 uppercase truncate">
                >>> WCAG_ACCESSIBILITY_AUDITOR_FOR_LARAVEL
            </div>

            <h1 class="font-mono font-black leading-none tracking-tight">
                <span class="block text-[clamp(3.5rem,11vw,9rem)] text-content">LENS FOR</span>
                <span class="block text-[clamp(3.5rem,11vw,9rem)] text-accent -mt-2 md:-mt-4">LARAVEL</span>
            </h1>

            <p
                class="mt-10 text-muted font-mono text-base max-w-xl mx-auto leading-relaxed">
                Local-first WCAG auditor for Blade, Livewire, React, Vue, and Inertia. Maps dynamic and nested DOM
                violations to source files, scans interactive UI states, and supports CI baselines.
            </p>
        </div>

        {{-- Install command --}}
        <div class="mt-10 max-w-2xl w-full">
            <div class="bg-page border-2 border-control flex items-stretch">
                <div
                    class="border-r-2 border-control px-5 flex items-center text-accent font-mono text-sm font-bold shrink-0">
                    $</div>
                <div
                    class="px-4 md:px-6 py-4 text-content font-mono text-xs md:text-sm flex-1 min-w-0 select-all truncate">
                    composer require webcrafts-studio/lens-for-laravel --dev
                </div>
                <button type="button" aria-label="Copy installation command"
                    onclick="copyCmd(this, 'composer require webcrafts-studio/lens-for-laravel --dev')"
                    class="border-l-2 border-control px-3 md:px-5 text-muted hover:text-content hover:bg-panel transition-colors font-mono text-xs font-bold uppercase tracking-widest cursor-pointer flex items-center justify-center shrink-0">
                    <span class="copy-label hidden md:inline">COPY</span>
                    <svg class="md:hidden w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" aria-label="Copy">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- CTAs --}}
        <div class="mt-6 mb-12 md:mb-0 flex items-center gap-4 flex-wrap justify-center">
            <a href="{{ route('docs') }}"
                class="bg-accent-solid text-on-accent border-2 border-accent-solid px-8 py-3 font-mono text-xs font-bold tracking-widest uppercase hover:bg-page hover:text-accent transition-colors">
                DOCUMENTATION →
            </a>
            <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                class="bg-transparent text-content border-2 border-control px-8 py-3 font-mono text-xs font-bold tracking-widest uppercase hover:border-content hover:bg-content hover:text-page transition-colors">
                GITHUB
            </a>
        </div>
        </div>

        </section>
    {{-- ====================================================== --}}
    {{-- STATS — "Lens by Numbers"                              --}}
    {{-- ====================================================== --}}
    <section class="bg-panel border-y-2 md:border-y-4 border-content">
        <div
            class="grid grid-cols-2 md:grid-cols-5 divide-x-0 md:divide-x-2 divide-y-2 md:divide-y-0 divide-content">

            <div class="p-8 col-span-2 md:col-span-1 flex flex-col justify-center">
                <div
                    class="text-xs font-mono font-bold tracking-[0.2em] text-muted uppercase leading-relaxed">
                    LENS FOR LARAVEL<br>BY NUMBERS
                </div>
            </div>

            <div class="p-8">
                <div class="text-5xl font-black font-mono text-content">5</div>
                <div class="text-xs font-mono tracking-widest text-muted mt-2 uppercase">UI
                    Languages</div>
            </div>

            <div class="p-8">
                <div class="text-5xl font-black font-mono text-content">3</div>
                <div class="text-xs font-mono tracking-widest text-muted mt-2 uppercase">WCAG
                    Levels</div>
            </div>

            <div class="p-8">
                <div class="font-black font-mono leading-none">
                    <span class="text-3xl text-content">BLADE</span>
                    <span class="text-3xl text-accent"> JS</span>
                </div>
                <div class="text-xs font-mono tracking-widest text-muted mt-2 uppercase">
                    Source Types</div>
            </div>

            <div class="p-8">
                <div class="font-black font-mono text-content leading-tight text-base">
                    GEMINI<br>OPENAI<br>ANTHROPIC
                </div>
                <div class="text-xs font-mono tracking-widest text-muted mt-2 uppercase">AI
                    Providers</div>
            </div>

        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- CORE FEATURES GRID                                      --}}
    {{-- ====================================================== --}}
    <section id="features" class="bg-page border-b-4 border-content">
        <div
            class="grid grid-cols-1 lg:grid-cols-3 divide-y-2 lg:divide-y-0 lg:divide-x-2 divide-content">

            {{-- Feature 01: Multi-mode Scanning --}}
            <div class="p-10">
                <div class="text-accent text-xs font-bold font-mono tracking-[0.3em] mb-6 uppercase">01 / SCAN MODES</div>
                <h3 class="font-mono font-black text-3xl text-content leading-tight">
                    Multi-mode<br>Scanning</h3>
                <p class="mt-4 text-muted font-mono text-base leading-relaxed">
                    Target a single page, a list of URLs, crawl your entire site, or scan states after UI interactions.
                    Enable JavaScript crawling for SPA and Inertia apps.
                </p>
                <div class="mt-8 flex flex-col gap-2">
                    <div
                        class="group border-2 border-control px-4 py-3 font-mono text-xs flex items-center justify-between hover:bg-content hover:text-page transition-colors cursor-default">
                        <span
                            class="font-bold text-content group-hover:text-page">SINGLE_URL</span>
                        <span
                            class="text-muted group-hover:text-page text-xs">lens:audit
                            https://site.test</span>
                    </div>
                    <div
                        class="group border-2 border-control px-4 py-3 font-mono text-xs flex items-center justify-between hover:bg-content hover:text-page transition-colors cursor-default">
                        <span
                            class="font-bold text-content group-hover:text-page">MULTIPLE_URLS</span>
                        <span
                            class="text-muted group-hover:text-page text-xs">lens:audit
                            url1 url2 url3</span>
                    </div>
                    <div
                        class="border-2 border-accent-solid bg-accent-solid text-on-accent px-4 py-3 font-mono text-xs flex items-center justify-between cursor-default">
                        <span class="font-bold">WHOLE_WEBSITE</span>
                        <span class="text-on-accent text-xs">--crawl flag</span>
                    </div>
                    <div
                        class="group border-2 border-control px-4 py-3 font-mono text-xs flex items-center justify-between hover:bg-content hover:text-page transition-colors cursor-default">
                        <span
                            class="font-bold text-content group-hover:text-page">INTERACTIVE_STATES</span>
                        <span
                            class="text-muted group-hover:text-page text-xs">click/type/wait</span>
                    </div>
                </div>
            </div>

            {{-- Feature 02: AI Fix Engine — intentionally always dark for contrast --}}
            <div class="p-10 bg-black text-white">
                <div class="text-terminal-red text-xs font-bold font-mono tracking-[0.3em] mb-6 uppercase">02 / AI ENGINE</div>
                <h3 class="font-mono font-black text-3xl text-white leading-tight">AI_FIX<br>Engine</h3>
                <p class="mt-4 text-terminal-muted font-mono text-base leading-relaxed">
                    Your chosen AI (local Ollama, Gemini, OpenAI, Anthropic, OpenRouter, xAI, DeepSeek, or Mistral) reads a focused element or component and generates a
                    minimal Blade, React, or Vue replacement. Review or edit each diff before applying, or prepare all
                    located Level A or AA fixes in a progressive review queue.
                </p>
                <div class="mt-8 border border-terminal-border">
                    <div class="border-b border-terminal-border px-4 py-2 flex items-center justify-between bg-terminal-panel">
                        <span class="text-terminal-red text-xs font-bold font-mono uppercase tracking-wider">BEFORE</span>
                        <span class="text-terminal-muted text-xs font-mono">js/Components/Footer.vue:112</span>
                    </div>
                    <div class="px-4 py-3 text-xs font-mono text-terminal-red leading-relaxed">
                        &lt;a href=""&gt;<br>
                        &nbsp;&nbsp;&lt;i class="fa-linkedin"&gt;&lt;/i&gt;<br>
                        &lt;/a&gt;
                    </div>
                    <div class="border-t border-b border-terminal-border px-4 py-2 bg-terminal-panel">
                        <span class="text-green-300 text-xs font-bold font-mono uppercase tracking-wider">AFTER — AI
                            FIX</span>
                    </div>
                    <div class="px-4 py-3 text-xs font-mono text-green-300 leading-relaxed">
                        &lt;a href="" aria-label="LinkedIn"&gt;<br>
                        &nbsp;&nbsp;&lt;i class="fa-linkedin"<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;aria-hidden="true"&gt;&lt;/i&gt;<br>
                        &lt;/a&gt;
                    </div>
                </div>
                <div class="mt-4 text-terminal-muted font-mono text-xs leading-relaxed">
                    - v3.3: Local AI Fix models through Ollama<br>
                    - v3.2: Named Blade routes · Nested markup · Selector context<br>
                    - v3.1: In-modal editor · Fix All A/AA · Live queue<br>
                    - Up to 3 suggestions generate concurrently · Per-item retry<br>
                    - Fresh re-scans · Stable issue actions · Better repeated-node mapping<br>
                    - Optional: PHP 8.3+ · Laravel 12+ · laravel/ai
                </div>
            </div>

            {{-- Feature 03: CLI First --}}
            <div class="p-10">
                <div class="text-accent text-xs font-bold font-mono tracking-[0.3em] mb-6 uppercase">03 / CLI FIRST</div>
                <h3 class="font-mono font-black text-3xl text-content leading-tight">CLI<br>First</h3>
                <p class="mt-4 text-muted font-mono text-base leading-relaxed">
                    A first-class Artisan command with selectable WCAG 2.0/2.1/2.2 standards, reusable interaction-state scripts, level filters, crawl mode, source mapping, SPA-friendly options,
                    thresholds, and baseline quality gates.
                </p>
                {{-- Always-dark code block (terminal) --}}
                <div class="mt-8 bg-black text-white p-5 font-mono text-xs leading-relaxed">
                    <div class="text-terminal-muted mb-3"># Regression-only CI quality gate</div>
                    <div><span class="text-terminal-red">$</span> <span class="text-terminal">php artisan lens:audit
                            \</span></div>
                    <div class="ml-5 text-terminal-muted">&nbsp;&nbsp;https://your-app.test \</div>
                    <div class="ml-5"><span class="text-yellow-300">--aa</span> <span class="text-terminal-subtle"># Level
                            A + AA</span></div>
                    <div class="ml-5"><span class="text-yellow-300">--crawl</span> <span class="text-terminal-subtle">#
                            Scan entire site</span></div>
                    <div class="ml-5"><span class="text-yellow-300">--fail-on-new</span> <span
                            class="text-terminal-subtle"># Fail on regressions</span></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach (['--wcag=2.2', '--states=path', '--a', '--aa', '--all', '--crawl', '--threshold=N', '--baseline', '--fail-on-new'] as $flag)
                        <span
                            class="border border-control px-2 py-1 font-mono text-xs text-muted">{{ $flag }}</span>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- CLI SHOWCASE — always dark (terminal window)           --}}
    {{-- ====================================================== --}}
    <section id="cli" class="bg-terminal-surface py-24 border-b border-terminal-divider">
        <div class="max-w-5xl mx-auto px-6">
            <div class="text-terminal-red text-xs font-bold font-mono tracking-[0.3em] mb-4 uppercase">TERMINAL PREVIEW</div>
            <h2 class="font-mono font-black text-terminal text-5xl mb-3 leading-tight">Diagnostic<br>Report</h2>
            <p class="font-mono text-terminal-muted text-base mb-12">
                Real output from <span class="text-terminal">php artisan lens:audit https://webcrafts.test --aa</span>
            </p>

            <div class="border-2 border-terminal-border">
                <div class="border-b-2 border-terminal-border px-5 py-3 flex items-center gap-4 bg-terminal-panel">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-300"></div>
                        <div class="w-3 h-3 rounded-full bg-green-300"></div>
                    </div>
                    <span class="text-terminal-muted font-mono text-xs ml-2 truncate">
                        zsh — php artisan lens:audit https://webcrafts.test --aa
                    </span>
                </div>

                <div class="p-6 space-y-3 font-mono text-xs overflow-x-auto">
                    <div class="text-terminal-muted">
                        Running Lens audit on: <span class="text-terminal">https://webcrafts.test</span>
                    </div>
                    <div class="text-terminal-muted">
                        Filter: <span class="text-yellow-300">WCAG A + AA</span> &nbsp;|&nbsp; Mode: <span
                            class="text-yellow-300">SINGLE_URL</span>
                    </div>
                    <div class="text-terminal-divider" aria-hidden="true">──────────────────────────────────────────────────────────────────────
                    </div>

                    <div class="pt-2">
                        <div class="text-terminal font-bold uppercase tracking-wider text-xs">DIAGNOSTIC REPORT</div>
                        <div class="text-terminal-muted text-xs mt-1">
                            TOTAL_VIOLATIONS: <span class="text-terminal-red font-bold text-xs">3</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 border border-terminal-border text-xs mt-1">
                        <div class="p-3 border-r border-terminal-border bg-red-950">
                            <div class="text-terminal-muted uppercase tracking-wider">A LEVEL</div>
                            <div class="text-terminal font-black text-2xl mt-1">3</div>
                        </div>
                        <div class="p-3 sm:border-r border-terminal-border">
                            <div class="text-terminal-muted uppercase tracking-wider">AA LEVEL</div>
                            <div class="text-terminal font-black text-2xl mt-1">0</div>
                        </div>
                        <div class="p-3 border-r border-t sm:border-t-0 border-terminal-border">
                            <div class="text-terminal-muted uppercase tracking-wider">AAA LEVEL</div>
                            <div class="text-terminal font-black text-2xl mt-1">17</div>
                        </div>
                        <div class="p-3 border-t sm:border-t-0 border-terminal-border">
                            <div class="text-terminal-muted uppercase tracking-wider">OTHER</div>
                            <div class="text-terminal font-black text-2xl mt-1">81</div>
                        </div>
                    </div>

                    <div class="text-terminal-divider" aria-hidden="true">──────────────────────────────────────────────────────────────────────
                    </div>

                    <div class="border border-terminal-border">
                        <div class="px-4 py-2.5 flex items-center gap-3 border-b border-terminal-border">
                            <span
                                class="bg-accent-solid text-on-accent text-xs font-bold px-2 py-0.5 uppercase shrink-0">WCAG
                                A</span>
                            <span class="text-terminal font-bold">link-name</span>
                            <span class="text-terminal-muted text-xs uppercase ml-auto">CRITICAL</span>
                        </div>
                        <div class="px-4 py-2 text-terminal-muted">Ensures links have discernible text</div>
                        <div class="px-4 pt-1 pb-0 text-terminal-muted text-xs uppercase tracking-wider">&gt;&gt;&gt;
                            FAILING_NODE</div>
                        <div
                            class="mx-4 my-2 px-3 py-2 bg-terminal-panel text-terminal-red border-l-2 border-terminal-red leading-relaxed">
                            &lt;a href="" class="footer__social"&gt;&lt;i
                            class="fa-brands fa-linkedin footer__social-icon"
                            aria-hidden="true"&gt;&lt;/i&gt;&lt;/a&gt;
                        </div>
                        <div
                            class="px-4 pb-2 flex flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between text-xs text-terminal-muted">
                            <span>SRC_LOC: <span
                                    class="text-terminal border border-terminal-border px-2 py-0.5 ml-1">vue js/Components/Footer.vue:112</span></span>
                            <span>CSS_SELECTOR: <span
                                    class="text-terminal">.footer__social[href=""]:nth-child(1)</span></span>
                        </div>
                    </div>

                    <div class="border border-terminal-border">
                        <div class="px-4 py-2.5 flex items-center gap-3 border-b border-terminal-border">
                            <span
                                class="bg-accent-solid text-on-accent text-xs font-bold px-2 py-0.5 uppercase shrink-0">WCAG
                                A</span>
                            <span class="text-terminal font-bold">button-name</span>
                            <span class="text-terminal-muted text-xs uppercase ml-auto">CRITICAL</span>
                        </div>
                        <div class="px-4 py-2 text-terminal-muted">Ensures buttons have discernible text</div>
                        <div class="px-4 pt-1 pb-0 text-terminal-muted text-xs uppercase tracking-wider">&gt;&gt;&gt;
                            FAILING_NODE</div>
                        <div class="mx-4 my-2 px-3 py-2 bg-terminal-panel text-terminal-red border-l-2 border-terminal-red">
                            &lt;button class="nav__toggle"&gt;&lt;/button&gt;
                        </div>
                        <div
                            class="px-4 pb-2 flex flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between text-xs text-terminal-muted">
                            <span>SRC_LOC: <span
                                    class="text-terminal border border-terminal-border px-2 py-0.5 ml-1">partials/nav.blade.php:45</span></span>
                            <span>CSS_SELECTOR: <span class="text-terminal">.nav__toggle</span></span>
                        </div>
                    </div>

                    <div class="text-terminal-divider" aria-hidden="true">──────────────────────────────────────────────────────────────────────
                    </div>
                    <div class="text-terminal-red text-xs pt-1">
                        ✗ Quality gate FAILED — 3 violations exceed threshold of 0 &nbsp;(exit code: 1)
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- DASHBOARD PREVIEW — always dark (shows dark app UI)    --}}
    {{-- ====================================================== --}}
    <section class="bg-terminal-surface py-24 border-b border-terminal-divider">
        <div class="max-w-5xl mx-auto px-6">
            <div class="text-terminal-red text-xs font-bold font-mono tracking-[0.3em] mb-4 uppercase">VISUAL INTERFACE</div>
            <h2 class="font-mono font-black text-terminal text-5xl mb-3 leading-tight">Dashboard<br>Preview</h2>
            <p class="font-mono text-terminal-muted text-base mb-12">
                Navigate to <span class="text-terminal">/lens-for-laravel/dashboard</span> after installation
            </p>

            <div class="border-2 border-terminal-border bg-terminal-surface">
                <div class="border-b border-terminal-border px-6 py-3.5 flex items-center justify-between">
                    <div class="font-mono font-bold text-terminal text-sm tracking-wide">
                        Lens<span class="text-terminal-red">ForLaravel</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="font-mono text-terminal-muted text-xs uppercase tracking-widest">REPOSITORY</span>
                        <div
                            class="w-8 h-8 border border-terminal-border flex items-center justify-center text-terminal-muted font-mono text-xs">
                            ◑</div>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <div class="border border-terminal-border p-5">
                        <div class="font-mono font-black text-terminal text-xs uppercase tracking-widest mb-1">TARGET
                            DESIGNATION</div>
                        <p class="font-mono text-terminal-muted text-xs mb-4 leading-relaxed">
                            Enter target URL for comprehensive accessibility analysis. This auditor utilizes Axe-core
                            via Spatie Browsershot to identify WCAG violations.<br>
                            <span class="text-terminal-muted">Scanning is restricted to your application's own domain.</span>
                        </p>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <div
                                class="border-2 border-terminal bg-terminal text-terminal-surface px-3 py-1 font-mono text-xs uppercase font-bold">
                                SINGLE_URL</div>
                            <div
                                class="border border-terminal-border text-terminal-muted px-3 py-1 font-mono text-xs uppercase">
                                WHOLE_WEBSITE</div>
                            <div
                                class="border border-terminal-border text-terminal-muted px-3 py-1 font-mono text-xs uppercase">
                                MULTIPLE_URLS</div>
                            <div
                                class="border border-terminal-border text-terminal-muted px-3 py-1 font-mono text-xs uppercase">
                                INTERACTIVE_STATES</div>
                        </div>
                        <div class="flex overflow-hidden">
                            <div
                                class="flex-1 min-w-0 border border-terminal-border border-r-0 flex items-center px-4 py-2.5">
                                <span class="text-terminal-red font-mono text-xs mr-2 shrink-0">›</span>
                                <span class="text-terminal-muted font-mono text-xs truncate">https://webcrafts.test</span>
                            </div>
                            <div
                                class="bg-accent-solid text-on-accent font-mono text-xs px-4 flex items-center font-bold tracking-widest uppercase shrink-0">
                                EXECUTE</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="font-mono font-black text-terminal text-xs uppercase tracking-widest">DIAGNOSTIC
                            REPORT</div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="font-mono text-terminal-muted text-xs">TOTAL_VIOLATIONS: <span
                                    class="text-terminal-red font-bold text-xs">101</span></span>
                            <div
                                class="border border-terminal-border text-terminal-muted font-mono text-xs px-3 py-1 uppercase tracking-wider shrink-0">
                                ↓ EXPORT PDF</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4">
                        <div class="border-2 border-terminal-red bg-red-950 p-3 md:p-4">
                            <div class="font-mono text-terminal-muted text-xs uppercase tracking-wider mb-2">A LEVEL</div>
                            <div class="font-mono font-black text-terminal text-3xl md:text-4xl">3</div>
                        </div>
                        <div class="border border-terminal-border border-l-0 p-3 md:p-4">
                            <div class="font-mono text-terminal-muted text-xs uppercase tracking-wider mb-2">AA LEVEL
                            </div>
                            <div class="font-mono font-black text-terminal text-3xl md:text-4xl">0</div>
                        </div>
                        <div class="border border-terminal-border md:border-l-0 border-t md:border-t-0 p-3 md:p-4">
                            <div class="font-mono text-terminal-muted text-xs uppercase tracking-wider mb-2">AAA LEVEL
                            </div>
                            <div class="font-mono font-black text-terminal text-3xl md:text-4xl">17</div>
                        </div>
                        <div class="border border-terminal-border border-l-0 border-t md:border-t-0 p-3 md:p-4">
                            <div class="font-mono text-terminal-muted text-xs uppercase tracking-wider mb-2">OTHER</div>
                            <div class="font-mono font-black text-terminal text-3xl md:text-4xl">81</div>
                        </div>
                    </div>

                    <div class="border border-terminal-border bg-terminal-panel px-4 py-3">
                        <span class="text-terminal-red font-mono text-xs font-bold">INFO: </span>
                        <span class="text-terminal-muted font-mono text-xs uppercase leading-relaxed">
                            LEVEL A IS THE MINIMUM LEVEL OF ACCESSIBILITY. THESE ISSUES ARE CRITICAL BLOCKERS FOR USERS
                            WITH DISABILITIES.
                        </span>
                    </div>

                    <div class="border border-terminal-border">
                        <div class="border-b border-terminal-border px-4 py-2 flex items-center justify-between">
                            <span class="font-mono text-terminal-muted text-xs uppercase tracking-wider">FILTERED LOGS:
                                WCAG2A</span>
                            <span class="font-mono text-terminal-red text-xs">SHOWING: 3</span>
                        </div>
                        <div class="divide-y divide-terminal-divider">
                            @foreach ([['rule' => 'link-name', 'desc' => 'Ensures links have discernible text', 'file' => 'blade partials/footer.blade.php:112'], ['rule' => 'button-name', 'desc' => 'Ensures buttons have discernible text', 'file' => 'react js/Pages/Dashboard.tsx:45'], ['rule' => 'image-alt', 'desc' => 'Ensures img elements have alternate text', 'file' => 'vue js/Components/Hero.vue:8']] as $violation)
                                <div class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2">
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span
                                                class="bg-accent-solid text-on-accent text-xs font-bold px-2 py-0.5 uppercase shrink-0">WCAG
                                                A</span>
                                            <span
                                                class="text-terminal font-bold text-xs whitespace-nowrap">{{ $violation['rule'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span
                                                class="border border-terminal-border text-terminal-muted text-xs px-2 py-0.5 font-mono uppercase whitespace-nowrap">AI
                                                FIX</span>
                                            <span
                                                class="text-terminal-muted text-xs font-mono uppercase whitespace-nowrap">VIEW
                                                DOCS →</span>
                                        </div>
                                    </div>
                                    <div class="text-terminal-muted font-mono text-xs mt-2">{{ $violation['desc'] }}
                                    </div>
                                    <div class="font-mono text-xs text-terminal-muted mt-2">SRC_LOC: <span
                                            class="text-terminal border border-terminal-border px-1.5 py-0.5 ml-1">{{ $violation['file'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- CTA — always red in both themes                        --}}
    {{-- ====================================================== --}}
    <section class="bg-accent-solid py-28 border-b-4 border-black">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <div class="text-white font-mono text-xs font-bold tracking-[0.3em] mb-10 uppercase">&gt;&gt;&gt; GET_STARTED
            </div>
            <h2 class="font-mono font-black text-white leading-none mb-8">
                <span class="block text-[clamp(3rem,8vw,6rem)]">MAKE YOUR</span>
                <span class="block text-[clamp(3rem,8vw,6rem)]">APP ACCESSIBLE</span>
            </h2>
            <p class="font-mono text-white text-base max-w-lg mx-auto mb-12 leading-relaxed">
                Install Lens for Laravel in seconds. Start catching WCAG violations your users never should have
                encountered.
            </p>
            <div class="bg-terminal-surface border-2 border-white flex items-stretch max-w-xl mx-auto mb-8">
                <div
                    class="border-r-2 border-white px-5 flex items-center text-terminal-red font-mono text-sm font-bold shrink-0">
                    $</div>
                <div class="px-6 py-4 text-white font-mono text-sm flex-1 text-left select-all">
                    composer require webcrafts-studio/lens-for-laravel --dev
                </div>
                <button type="button" aria-label="Copy installation command"
                    onclick="copyCmd(this, 'composer require webcrafts-studio/lens-for-laravel --dev')"
                    class="border-l-2 border-white px-5 text-white hover:bg-white hover:text-black transition-colors font-mono text-xs font-bold uppercase tracking-widest cursor-pointer">
                    COPY
                </button>
            </div>
            <div class="flex items-center justify-center gap-4 flex-wrap">
                <a href="{{ route('docs') }}"
                    class="bg-white text-black border-2 border-white px-10 py-3 font-mono text-xs font-bold tracking-widest uppercase hover:bg-transparent hover:text-white transition-colors">
                    DOCUMENTATION →
                </a>
                <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                    class="border-2 border-white text-white px-10 py-3 font-mono text-xs font-bold tracking-widest uppercase hover:bg-white hover:text-accent-solid transition-colors">
                    GITHUB
                </a>
            </div>
        </div>
    </section>

    </main>

    {{-- ====================================================== --}}
    {{-- FOOTER — always dark in both themes                    --}}
    {{-- ====================================================== --}}
    <footer class="bg-terminal-surface border-t border-terminal-divider py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-10">
                <div>
                    <div class="font-mono font-black leading-none mb-3">
                        <span class="text-white text-3xl tracking-tight">LENS FOR</span>
                        <span class="text-terminal-red text-3xl tracking-tight"> LARAVEL</span>
                    </div>
                    <div class="text-terminal-muted font-mono text-xs tracking-widest uppercase">
                        WCAG Accessibility Auditor · Blade · Livewire · React · Vue · AI
                    </div>
                </div>
                <nav class="flex flex-wrap items-center gap-4 md:gap-8" aria-label="Footer navigation">
                    <a href="{{ route('docs') }}"
                        class="text-terminal-muted hover:text-white font-mono text-xs uppercase tracking-widest transition-colors">Documentation</a>
                    <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                        class="text-terminal-muted hover:text-white font-mono text-xs uppercase tracking-widest transition-colors">GitHub</a>
                    <a href="https://github.com/webcrafts-studio/lens-for-laravel/issues"
                        class="text-terminal-muted hover:text-white font-mono text-xs uppercase tracking-widest transition-colors">Issues</a>
                    <a href="https://buycoffee.to/jakub-lipinski" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 border border-terminal-red px-3 py-1.5 text-terminal-red hover:bg-terminal-red hover:text-black font-mono text-xs uppercase tracking-widest transition-colors">
                        <span aria-hidden="true">☕</span>
                        <span>Support my work</span>
                        <span aria-hidden="true">↗</span>
                    </a>
                </nav>
            </div>

            <div
                class="border-t border-terminal-divider mt-12 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="space-y-3 text-center md:text-left">
                    <div class="text-terminal-muted font-mono text-xs tracking-widest uppercase">
                        A / AA / AAA &nbsp;·&nbsp; Laravel 10 / 11 / 12 / 13 &nbsp;·&nbsp; PHP 8.2+
                    </div>
                    <div class="font-mono text-xs text-terminal-muted tracking-wider">
                        Created by
                        <a href="https://lipinskijakub.pl/" target="_blank" rel="noopener noreferrer"
                            class="text-terminal hover:text-terminal-red transition-colors">Jakub Lipiński</a>
                        <span aria-hidden="true">—</span>
                        <a href="https://webcrafts.pl/" target="_blank" rel="noopener noreferrer"
                            class="text-terminal hover:text-terminal-red transition-colors">Webcrafts.pl</a>
                    </div>
                </div>
                <div class="text-terminal-muted font-mono text-xs text-center md:text-right max-w-md leading-relaxed">
                    axe-core automates many high-confidence checks, but neither axe-core nor Lens proves full WCAG
                    conformance. Manual testing remains required.
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
