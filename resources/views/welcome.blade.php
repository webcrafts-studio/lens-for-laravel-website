<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lens for Laravel — WCAG Accessibility Auditor</title>
    <meta name="description"
        content="Plug-and-play WCAG compliance scanner for Laravel. Identifies accessibility violations, maps them to Blade files, and auto-fixes with Gemini AI.">

    {{-- Anti-FOUC: apply saved theme before first paint --}}
    <script>
        (function() {
            var saved = localStorage.getItem('lens-theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();

        function toggleTheme() {
            var isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('lens-theme', isDark ? 'dark' : 'light');
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

<body class="bg-white dark:bg-black font-mono antialiased">

    {{-- ====================================================== --}}
    {{-- NAVIGATION                                              --}}
    {{-- ====================================================== --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white dark:bg-black border-b-2 border-black/10 dark:border-white/10">
        <div class="max-w-7xl mx-auto px-6 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-black dark:text-white font-black text-lg tracking-[0.15em] uppercase">LENS FOR</span>
                <span class="text-[#e53e3e] font-black text-lg tracking-[0.15em] uppercase">LARAVEL</span>
                <span
                    class="ml-2 hidden sm:inline text-black/20 dark:text-white/20 text-[10px] font-mono border border-black/20 dark:border-white/20 px-1.5 py-0.5 leading-none">v1.0</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="#features"
                    class="hidden md:block text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white text-xs font-mono tracking-widest transition-colors uppercase">Features</a>
                <a href="#cli"
                    class="hidden md:block text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white text-xs font-mono tracking-widest transition-colors uppercase">CLI</a>

                {{-- Theme toggle --}}
                <button onclick="toggleTheme()" title="Toggle theme"
                    class="w-9 h-9 flex items-center justify-center border-2 border-black/20 dark:border-white/20 hover:border-black dark:hover:border-white text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors text-base cursor-pointer">
                    <span class="dark:hidden" aria-label="Switch to dark mode">☾</span>
                    <span class="hidden dark:inline" aria-label="Switch to light mode">☀</span>
                </button>

                <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                    class="text-black dark:text-white font-mono text-[10px] md:text-xs border-2 border-black/40 dark:border-white/40 hover:border-black dark:hover:border-white px-3 md:px-4 py-2 hover:bg-black dark:hover:bg-white hover:text-white dark:hover:text-black transition-colors uppercase tracking-widest">
                    GitHub →
                </a>
            </div>
        </div>
    </nav>

    {{-- ====================================================== --}}
    {{-- HERO                                                    --}}
    {{-- ====================================================== --}}
    <section
        class="bg-white dark:bg-black min-h-screen flex flex-col items-center justify-center pt-14 px-6 relative overflow-hidden">

        {{-- Grid backgrounds (light vs dark) --}}
        <div class="dark:hidden absolute inset-0 opacity-[0.05] pointer-events-none"
            style="background-image: linear-gradient(#000 1px, transparent 1px), linear-gradient(90deg, #000 1px, transparent 1px); background-size: 48px 48px;">
        </div>
        <div class="hidden dark:block absolute inset-0 opacity-[0.03] pointer-events-none"
            style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 48px 48px;">
        </div>

        {{-- Corner bracket frame --}}
        <div
            class="relative border border-black/10 dark:border-white/10 px-8 py-16 md:px-20 md:py-20 max-w-5xl w-full text-center">
            <div
                class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-[#e53e3e] -translate-x-px -translate-y-px">
            </div>
            <div
                class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-[#e53e3e] translate-x-px -translate-y-px">
            </div>
            <div
                class="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-[#e53e3e] -translate-x-px translate-y-px">
            </div>
            <div
                class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-[#e53e3e] translate-x-px translate-y-px">
            </div>

            <div
                class="text-[#e53e3e] text-[10px] font-mono tracking-normal md:tracking-[0.4em] mb-10 uppercase truncate">
                >>> WCAG_ACCESSIBILITY_AUDITOR_FOR_LARAVEL
            </div>

            <h1 class="font-mono font-black leading-none tracking-tight">
                <span class="block text-[clamp(3.5rem,11vw,9rem)] text-black dark:text-white">LARAVEL</span>
                <span class="block text-[clamp(3.5rem,11vw,9rem)] text-[#e53e3e] -mt-2 md:-mt-4">LENS</span>
            </h1>

            <p
                class="mt-10 text-black/50 dark:text-white/40 font-mono text-sm md:text-base max-w-xl mx-auto leading-relaxed">
                Plug-and-play WCAG compliance scanner. Identifies violations, maps them to Blade files,
                and auto-fixes with Gemini AI.
            </p>
        </div>

        {{-- Install command --}}
        <div class="mt-10 max-w-2xl w-full">
            <div class="bg-white dark:bg-black border-2 border-black/20 dark:border-white/20 flex items-stretch">
                <div
                    class="border-r-2 border-black/20 dark:border-white/20 px-5 flex items-center text-[#e53e3e] font-mono text-sm shrink-0">
                    $</div>
                <div
                    class="px-4 md:px-6 py-4 text-black dark:text-white font-mono text-xs md:text-sm flex-1 min-w-0 select-all truncate">
                    composer require webcrafts-studio/lens-for-laravel --dev
                </div>
                <button onclick="copyCmd(this, 'composer require webcrafts-studio/lens-for-laravel --dev')"
                    class="border-l-2 border-black/20 dark:border-white/20 px-3 md:px-5 text-black/30 dark:text-white/30 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors font-mono text-[10px] uppercase tracking-widest cursor-pointer flex items-center justify-center shrink-0">
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
                class="bg-[#e53e3e] text-white border-2 border-[#e53e3e] px-8 py-3 font-mono text-xs font-bold tracking-widest uppercase hover:bg-transparent hover:text-[#e53e3e] transition-colors">
                DOCUMENTATION →
            </a>
            <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                class="bg-transparent text-black dark:text-white border-2 border-black/30 dark:border-white/30 px-8 py-3 font-mono text-xs font-bold tracking-widest uppercase hover:border-black dark:hover:border-white transition-colors">
                GITHUB
            </a>
        </div>

    </section>

    {{-- ====================================================== --}}
    {{-- STATS — "Lens by Numbers"                              --}}
    {{-- ====================================================== --}}
    <section class="bg-white dark:bg-zinc-950 border-y-2 md:border-y-4 border-black dark:border-white/10">
        <div
            class="grid grid-cols-2 md:grid-cols-5 divide-x-0 md:divide-x-2 divide-y-2 md:divide-y-0 divide-black dark:divide-white/10">

            <div class="p-8 col-span-2 md:col-span-1 flex flex-col justify-center">
                <div
                    class="text-[10px] font-mono font-bold tracking-[0.25em] text-black/40 dark:text-white/40 uppercase leading-relaxed">
                    LENS FOR LARAVEL<br>BY NUMBERS
                </div>
            </div>

            <div class="p-8">
                <div class="text-5xl font-black font-mono text-black dark:text-white">71</div>
                <div class="text-[10px] font-mono tracking-widest text-black/40 dark:text-white/40 mt-2 uppercase">Tests
                    Passing</div>
            </div>

            <div class="p-8">
                <div class="text-5xl font-black font-mono text-black dark:text-white">3</div>
                <div class="text-[10px] font-mono tracking-widest text-black/40 dark:text-white/40 mt-2 uppercase">WCAG
                    Levels</div>
            </div>

            <div class="p-8">
                <div class="font-black font-mono leading-none">
                    <span class="text-3xl text-black dark:text-white">WCAG</span>
                    <span class="text-3xl text-[#e53e3e]"> 2.2</span>
                </div>
                <div class="text-[10px] font-mono tracking-widest text-black/40 dark:text-white/40 mt-2 uppercase">
                    Compliant</div>
            </div>

            <div class="p-8">
                <div class="text-3xl font-black font-mono text-black dark:text-white">GEMINI</div>
                <div class="text-[10px] font-mono tracking-widest text-black/40 dark:text-white/40 mt-2 uppercase">AI
                    Powered Fixes</div>
            </div>

        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- CORE FEATURES GRID                                      --}}
    {{-- ====================================================== --}}
    <section id="features" class="bg-white dark:bg-black border-b-4 border-black dark:border-white/10">
        <div
            class="grid grid-cols-1 lg:grid-cols-3 divide-y-2 lg:divide-y-0 lg:divide-x-2 divide-black dark:divide-white/10">

            {{-- Feature 01: Multi-mode Scanning --}}
            <div class="p-10">
                <div class="text-[#e53e3e] text-[10px] font-mono tracking-[0.4em] mb-6 uppercase">01 / SCAN MODES</div>
                <h3 class="font-mono font-black text-3xl text-black dark:text-white leading-tight">
                    Multi-mode<br>Scanning</h3>
                <p class="mt-4 text-black/50 dark:text-white/50 font-mono text-sm leading-relaxed">
                    Target a single page, a list of URLs, or let Lens crawl your entire site via sitemap.xml and BFS
                    link discovery. Up to 50 pages by default.
                </p>
                <div class="mt-8 flex flex-col gap-2">
                    <div
                        class="group border-2 border-black dark:border-white/20 px-4 py-3 font-mono text-xs flex items-center justify-between hover:bg-black dark:hover:bg-white hover:text-white dark:hover:text-black transition-colors cursor-default">
                        <span
                            class="font-bold text-black dark:text-white group-hover:text-white dark:group-hover:text-black">SINGLE_URL</span>
                        <span
                            class="text-black/30 dark:text-white/30 group-hover:text-white/50 dark:group-hover:text-black/50 text-[10px]">lens:audit
                            https://site.test</span>
                    </div>
                    <div
                        class="group border-2 border-black dark:border-white/20 px-4 py-3 font-mono text-xs flex items-center justify-between hover:bg-black dark:hover:bg-white hover:text-white dark:hover:text-black transition-colors cursor-default">
                        <span
                            class="font-bold text-black dark:text-white group-hover:text-white dark:group-hover:text-black">MULTIPLE_URLS</span>
                        <span
                            class="text-black/30 dark:text-white/30 group-hover:text-white/50 dark:group-hover:text-black/50 text-[10px]">lens:audit
                            url1 url2 url3</span>
                    </div>
                    <div
                        class="border-2 border-[#e53e3e] bg-[#e53e3e] text-white px-4 py-3 font-mono text-xs flex items-center justify-between cursor-default">
                        <span class="font-bold">WHOLE_WEBSITE</span>
                        <span class="text-white/60 text-[10px]">--crawl flag</span>
                    </div>
                </div>
            </div>

            {{-- Feature 02: AI Fix Engine — intentionally always dark for contrast --}}
            <div class="p-10 bg-black text-white">
                <div class="text-[#e53e3e] text-[10px] font-mono tracking-[0.4em] mb-6 uppercase">02 / AI ENGINE</div>
                <h3 class="font-mono font-black text-3xl text-white leading-tight">AI_FIX<br>Engine</h3>
                <p class="mt-4 text-white/60 font-mono text-sm leading-relaxed">
                    Gemini AI reads ±20 lines of context around the failing element and generates a precise, structured
                    Blade fix. Apply it instantly from the dashboard.
                </p>
                <div class="mt-8 border border-white/10">
                    <div class="border-b border-white/10 px-4 py-2 flex items-center justify-between bg-white/5">
                        <span class="text-red-400 text-[10px] font-mono uppercase tracking-wider">BEFORE</span>
                        <span class="text-white/50 text-[10px] font-mono">partials/footer.blade.php:112</span>
                    </div>
                    <div class="px-4 py-3 text-[11px] font-mono text-red-400 leading-relaxed">
                        &lt;a href=""&gt;<br>
                        &nbsp;&nbsp;&lt;i class="fa-linkedin"&gt;&lt;/i&gt;<br>
                        &lt;/a&gt;
                    </div>
                    <div class="border-t border-b border-white/10 px-4 py-2 bg-white/5">
                        <span class="text-green-400 text-[10px] font-mono uppercase tracking-wider">AFTER — AI
                            FIX</span>
                    </div>
                    <div class="px-4 py-3 text-[11px] font-mono text-green-400 leading-relaxed">
                        &lt;a href="" aria-label="LinkedIn"&gt;<br>
                        &nbsp;&nbsp;&lt;i class="fa-linkedin"<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;aria-hidden="true"&gt;&lt;/i&gt;<br>
                        &lt;/a&gt;
                    </div>
                </div>
                <div class="mt-4 text-white/50 font-mono text-[10px] leading-relaxed">
                    → Reads Blade context · Structured AI response<br>
                    → Apply fix directly from dashboard
                </div>
            </div>

            {{-- Feature 03: CLI First --}}
            <div class="p-10">
                <div class="text-[#e53e3e] text-[10px] font-mono tracking-[0.4em] mb-6 uppercase">03 / CLI FIRST</div>
                <h3 class="font-mono font-black text-3xl text-black dark:text-white leading-tight">CLI<br>First</h3>
                <p class="mt-4 text-black/50 dark:text-white/50 font-mono text-sm leading-relaxed">
                    A first-class Artisan command with granular WCAG level filtering and a CI/CD quality gate via the
                    threshold flag.
                </p>
                {{-- Always-dark code block (terminal) --}}
                <div class="mt-8 bg-black text-white p-5 font-mono text-xs leading-relaxed">
                    <div class="text-white/30 mb-3"># Full audit with CI quality gate</div>
                    <div><span class="text-[#e53e3e]">$</span> <span class="text-white">php artisan lens:audit
                            \</span></div>
                    <div class="ml-5 text-white/60">&nbsp;&nbsp;https://your-app.test \</div>
                    <div class="ml-5"><span class="text-yellow-400">--aa</span> <span class="text-white/30"># Level
                            A + AA</span></div>
                    <div class="ml-5"><span class="text-yellow-400">--crawl</span> <span class="text-white/30">#
                            Scan entire site</span></div>
                    <div class="ml-5"><span class="text-yellow-400">--threshold=0</span> <span
                            class="text-white/30"># Fail CI</span></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach (['--a', '--aa', '--all', '--crawl', '--threshold=N'] as $flag)
                        <span
                            class="border border-black/20 dark:border-white/20 px-2 py-1 font-mono text-[10px] text-black/40 dark:text-white/40">{{ $flag }}</span>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- CLI SHOWCASE — always dark (terminal window)           --}}
    {{-- ====================================================== --}}
    <section id="cli" class="bg-black py-24 border-b border-white/10">
        <div class="max-w-5xl mx-auto px-6">
            <div class="text-[#e53e3e] text-[10px] font-mono tracking-[0.4em] mb-4 uppercase">TERMINAL PREVIEW</div>
            <h2 class="font-mono font-black text-white text-5xl mb-3 leading-tight">Diagnostic<br>Report</h2>
            <p class="font-mono text-white/30 text-sm mb-12">
                Real output from <span class="text-white">php artisan lens:audit https://webcrafts.test --aa</span>
            </p>

            <div class="border-2 border-white/20">
                <div class="border-b-2 border-white/20 px-5 py-3 flex items-center gap-4 bg-white/[0.03]">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-[#e53e3e]/70"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500/70"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
                    </div>
                    <span class="text-white/50 font-mono text-[10px] ml-2 truncate">
                        zsh — php artisan lens:audit https://webcrafts.test --aa
                    </span>
                </div>

                <div class="p-6 space-y-3 font-mono text-xs overflow-x-auto">
                    <div class="text-white/30">
                        Running Lens audit on: <span class="text-white">https://webcrafts.test</span>
                    </div>
                    <div class="text-white/30">
                        Filter: <span class="text-yellow-400">WCAG A + AA</span> &nbsp;|&nbsp; Mode: <span
                            class="text-yellow-400">SINGLE_URL</span>
                    </div>
                    <div class="text-white/10">──────────────────────────────────────────────────────────────────────
                    </div>

                    <div class="pt-2">
                        <div class="text-white font-bold uppercase tracking-wider text-xs">DIAGNOSTIC REPORT</div>
                        <div class="text-white/30 text-[10px] mt-1">
                            TOTAL_VIOLATIONS: <span class="text-[#e53e3e] font-bold text-xs">3</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 border border-white/20 text-[10px] mt-1">
                        <div class="p-3 border-r border-white/20 bg-[#e53e3e]/10">
                            <div class="text-white/40 uppercase tracking-wider">A LEVEL</div>
                            <div class="text-white font-black text-2xl mt-1">3</div>
                        </div>
                        <div class="p-3 sm:border-r border-white/20">
                            <div class="text-white/40 uppercase tracking-wider">AA LEVEL</div>
                            <div class="text-white font-black text-2xl mt-1">0</div>
                        </div>
                        <div class="p-3 border-r border-t sm:border-t-0 border-white/20">
                            <div class="text-white/40 uppercase tracking-wider">AAA LEVEL</div>
                            <div class="text-white font-black text-2xl mt-1">17</div>
                        </div>
                        <div class="p-3 border-t sm:border-t-0 border-white/20">
                            <div class="text-white/40 uppercase tracking-wider">OTHER</div>
                            <div class="text-white font-black text-2xl mt-1">81</div>
                        </div>
                    </div>

                    <div class="text-white/10">──────────────────────────────────────────────────────────────────────
                    </div>

                    <div class="border border-white/10">
                        <div class="px-4 py-2.5 flex items-center gap-3 border-b border-white/10">
                            <span
                                class="bg-[#e53e3e] text-white text-[9px] font-bold px-2 py-0.5 uppercase shrink-0">WCAG
                                A</span>
                            <span class="text-white font-bold">link-name</span>
                            <span class="text-white/50 text-[9px] uppercase ml-auto">CRITICAL</span>
                        </div>
                        <div class="px-4 py-2 text-white/60">Ensures links have discernible text</div>
                        <div class="px-4 pt-1 pb-0 text-white/50 text-[9px] uppercase tracking-wider">&gt;&gt;&gt;
                            FAILING_NODE</div>
                        <div
                            class="mx-4 my-2 px-3 py-2 bg-white/[0.03] text-red-400 border-l-2 border-[#e53e3e] leading-relaxed">
                            &lt;a href="" class="footer__social"&gt;&lt;i
                            class="fa-brands fa-linkedin footer__social-icon"
                            aria-hidden="true"&gt;&lt;/i&gt;&lt;/a&gt;
                        </div>
                        <div
                            class="px-4 pb-2 flex flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between text-[9px] text-white/50">
                            <span>SRC_LOC: <span
                                    class="text-white/70 border border-white/20 px-2 py-0.5 ml-1">partials/footer.blade.php:112</span></span>
                            <span>CSS_SELECTOR: <span
                                    class="text-white/70">.footer__social[href=""]:nth-child(1)</span></span>
                        </div>
                    </div>

                    <div class="border border-white/10">
                        <div class="px-4 py-2.5 flex items-center gap-3 border-b border-white/10">
                            <span
                                class="bg-[#e53e3e] text-white text-[9px] font-bold px-2 py-0.5 uppercase shrink-0">WCAG
                                A</span>
                            <span class="text-white font-bold">button-name</span>
                            <span class="text-white/50 text-[9px] uppercase ml-auto">CRITICAL</span>
                        </div>
                        <div class="px-4 py-2 text-white/60">Ensures buttons have discernible text</div>
                        <div class="px-4 pt-1 pb-0 text-white/50 text-[9px] uppercase tracking-wider">&gt;&gt;&gt;
                            FAILING_NODE</div>
                        <div class="mx-4 my-2 px-3 py-2 bg-white/[0.03] text-red-400 border-l-2 border-[#e53e3e]">
                            &lt;button class="nav__toggle"&gt;&lt;/button&gt;
                        </div>
                        <div
                            class="px-4 pb-2 flex flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between text-[9px] text-white/50">
                            <span>SRC_LOC: <span
                                    class="text-white/70 border border-white/20 px-2 py-0.5 ml-1">partials/nav.blade.php:45</span></span>
                            <span>CSS_SELECTOR: <span class="text-white/70">.nav__toggle</span></span>
                        </div>
                    </div>

                    <div class="text-white/10">──────────────────────────────────────────────────────────────────────
                    </div>
                    <div class="text-[#e53e3e] text-[10px] pt-1">
                        ✗ Quality gate FAILED — 3 violations exceed threshold of 0 &nbsp;(exit code: 1)
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- DASHBOARD PREVIEW — always dark (shows dark app UI)    --}}
    {{-- ====================================================== --}}
    <section class="bg-black py-24 border-b border-white/10">
        <div class="max-w-5xl mx-auto px-6">
            <div class="text-[#e53e3e] text-[10px] font-mono tracking-[0.4em] mb-4 uppercase">VISUAL INTERFACE</div>
            <h2 class="font-mono font-black text-white text-5xl mb-3 leading-tight">Dashboard<br>Preview</h2>
            <p class="font-mono text-white/30 text-sm mb-12">
                Navigate to <span class="text-white">/lens-for-laravel/dashboard</span> after installation
            </p>

            <div class="border-2 border-white/20 bg-[#0d0d0d]">
                <div class="border-b border-white/10 px-6 py-3.5 flex items-center justify-between">
                    <div class="font-mono font-bold text-white text-sm tracking-wide">
                        Lens<span class="text-[#e53e3e]">ForLaravel</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="font-mono text-white/50 text-[10px] uppercase tracking-widest">REPOSITORY</span>
                        <div
                            class="w-8 h-8 border border-white/10 flex items-center justify-center text-white/50 font-mono text-xs">
                            ◑</div>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <div class="border border-white/10 p-5">
                        <div class="font-mono font-black text-white text-[10px] uppercase tracking-widest mb-1">TARGET
                            DESIGNATION</div>
                        <p class="font-mono text-white/50 text-[10px] mb-4 leading-relaxed">
                            Enter target URL for comprehensive accessibility analysis. This auditor utilizes Axe-core
                            via Spatie Browsershot to identify WCAG violations.
                        </p>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <div
                                class="border-2 border-white bg-white text-black px-3 py-1 font-mono text-[10px] uppercase font-bold">
                                SINGLE_URL</div>
                            <div
                                class="border border-white/20 text-white/30 px-3 py-1 font-mono text-[10px] uppercase">
                                WHOLE_WEBSITE</div>
                            <div
                                class="border border-white/20 text-white/30 px-3 py-1 font-mono text-[10px] uppercase">
                                MULTIPLE_URLS</div>
                        </div>
                        <div class="flex overflow-hidden">
                            <div
                                class="flex-1 min-w-0 border border-white/20 border-r-0 flex items-center px-4 py-2.5">
                                <span class="text-[#e53e3e] font-mono text-xs mr-2 shrink-0">›</span>
                                <span class="text-white/30 font-mono text-xs truncate">https://webcrafts.test</span>
                            </div>
                            <div
                                class="bg-[#e53e3e] text-white font-mono text-[10px] px-4 flex items-center font-bold tracking-widest uppercase shrink-0">
                                EXECUTE</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="font-mono font-black text-white text-[10px] uppercase tracking-widest">DIAGNOSTIC
                            REPORT</div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="font-mono text-white/50 text-[10px]">TOTAL_VIOLATIONS: <span
                                    class="text-[#e53e3e] font-bold text-xs">101</span></span>
                            <div
                                class="border border-white/20 text-white/30 font-mono text-[10px] px-3 py-1 uppercase tracking-wider shrink-0">
                                ↓ EXPORT PDF</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4">
                        <div class="border-2 border-[#e53e3e] bg-[#e53e3e]/10 p-3 md:p-4">
                            <div class="font-mono text-white/40 text-[9px] uppercase tracking-wider mb-2">A LEVEL</div>
                            <div class="font-mono font-black text-white text-3xl md:text-4xl">3</div>
                        </div>
                        <div class="border border-white/15 border-l-0 p-3 md:p-4">
                            <div class="font-mono text-white/40 text-[9px] uppercase tracking-wider mb-2">AA LEVEL
                            </div>
                            <div class="font-mono font-black text-white text-3xl md:text-4xl">0</div>
                        </div>
                        <div class="border border-white/15 md:border-l-0 border-t md:border-t-0 p-3 md:p-4">
                            <div class="font-mono text-white/40 text-[9px] uppercase tracking-wider mb-2">AAA LEVEL
                            </div>
                            <div class="font-mono font-black text-white text-3xl md:text-4xl">17</div>
                        </div>
                        <div class="border border-white/15 border-l-0 border-t md:border-t-0 p-3 md:p-4">
                            <div class="font-mono text-white/40 text-[9px] uppercase tracking-wider mb-2">OTHER</div>
                            <div class="font-mono font-black text-white text-3xl md:text-4xl">81</div>
                        </div>
                    </div>

                    <div class="border border-white/10 bg-white/[0.02] px-4 py-3">
                        <span class="text-[#e53e3e] font-mono text-[10px] font-bold">INFO: </span>
                        <span class="text-white/60 font-mono text-[10px] uppercase leading-relaxed">
                            LEVEL A IS THE MINIMUM LEVEL OF ACCESSIBILITY. THESE ISSUES ARE CRITICAL BLOCKERS FOR USERS
                            WITH DISABILITIES.
                        </span>
                    </div>

                    <div class="border border-white/10">
                        <div class="border-b border-white/10 px-4 py-2 flex items-center justify-between">
                            <span class="font-mono text-white/30 text-[10px] uppercase tracking-wider">FILTERED LOGS:
                                WCAG2A</span>
                            <span class="font-mono text-[#e53e3e] text-[10px]">SHOWING: 3</span>
                        </div>
                        <div class="divide-y divide-white/5">
                            @foreach ([['rule' => 'link-name', 'desc' => 'Ensures links have discernible text', 'file' => 'partials/footer.blade.php:112'], ['rule' => 'button-name', 'desc' => 'Ensures buttons have discernible text', 'file' => 'partials/nav.blade.php:45'], ['rule' => 'image-alt', 'desc' => 'Ensures img elements have alternate text', 'file' => 'components/hero.blade.php:8']] as $violation)
                                <div class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2">
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span
                                                class="bg-[#e53e3e] text-white text-[9px] font-bold px-2 py-0.5 uppercase shrink-0">WCAG
                                                A</span>
                                            <span
                                                class="text-white font-bold text-[10px] whitespace-nowrap">{{ $violation['rule'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span
                                                class="border border-white/10 text-white/30 text-[9px] px-2 py-0.5 font-mono uppercase whitespace-nowrap">AI
                                                FIX</span>
                                            <span
                                                class="text-white/50 text-[9px] font-mono uppercase whitespace-nowrap">VIEW
                                                DOCS →</span>
                                        </div>
                                    </div>
                                    <div class="text-white/60 font-mono text-[10px] mt-2">{{ $violation['desc'] }}
                                    </div>
                                    <div class="font-mono text-[9px] text-white/50 mt-2">SRC_LOC: <span
                                            class="text-white/70 border border-white/20 px-1.5 py-0.5 ml-1">{{ $violation['file'] }}</span>
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
    <section class="bg-[#e53e3e] py-28 border-b-4 border-black">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <div class="text-white/50 font-mono text-[10px] tracking-[0.4em] mb-10 uppercase">&gt;&gt;&gt; GET_STARTED
            </div>
            <h2 class="font-mono font-black text-white leading-none mb-8">
                <span class="block text-[clamp(3rem,8vw,6rem)]">MAKE YOUR</span>
                <span class="block text-[clamp(3rem,8vw,6rem)]">APP ACCESSIBLE</span>
            </h2>
            <p class="font-mono text-white/60 text-sm max-w-lg mx-auto mb-12 leading-relaxed">
                Install Lens for Laravel in seconds. Start catching WCAG violations your users never should have
                encountered.
            </p>
            <div class="bg-black border-2 border-white/20 flex items-stretch max-w-xl mx-auto mb-8">
                <div
                    class="border-r-2 border-white/20 px-5 flex items-center text-[#e53e3e] font-mono text-sm shrink-0">
                    $</div>
                <div class="px-6 py-4 text-white font-mono text-sm flex-1 text-left select-all">
                    composer require webcrafts-studio/lens-for-laravel --dev
                </div>
                <button onclick="copyCmd(this, 'composer require webcrafts-studio/lens-for-laravel --dev')"
                    class="border-l-2 border-white/20 px-5 text-white/40 hover:text-white hover:bg-white/5 transition-colors font-mono text-[10px] uppercase tracking-widest cursor-pointer">
                    COPY
                </button>
            </div>
            <div class="flex items-center justify-center gap-4 flex-wrap">
                <a href="{{ route('docs') }}"
                    class="bg-white text-black border-2 border-white px-10 py-3 font-mono text-xs font-bold tracking-widest uppercase hover:bg-transparent hover:text-white transition-colors">
                    DOCUMENTATION →
                </a>
                <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                    class="border-2 border-white/40 text-white px-10 py-3 font-mono text-xs font-bold tracking-widest uppercase hover:border-white transition-colors">
                    GITHUB
                </a>
            </div>
        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- FOOTER — always dark in both themes                    --}}
    {{-- ====================================================== --}}
    <footer class="bg-black border-t border-white/10 py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-10">
                <div>
                    <div class="font-mono font-black leading-none mb-3">
                        <span class="text-white text-3xl tracking-tight">LARAVEL</span>
                        <span class="text-[#e53e3e] text-3xl tracking-tight"> LENS</span>
                    </div>
                    <div class="text-white/40 font-mono text-[10px] tracking-widest uppercase">
                        WCAG Accessibility Auditor · Powered by Axe-core &amp; Gemini AI
                    </div>
                </div>
                <nav class="flex items-center gap-8" aria-label="Footer navigation">
                    <a href="{{ route('docs') }}"
                        class="text-white/30 hover:text-white font-mono text-[10px] uppercase tracking-widest transition-colors">Documentation</a>
                    <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                        class="text-white/30 hover:text-white font-mono text-[10px] uppercase tracking-widest transition-colors">GitHub</a>
                    <a href="https://github.com/webcrafts-studio/lens-for-laravel/issues"
                        class="text-white/30 hover:text-white font-mono text-[10px] uppercase tracking-widest transition-colors">Issues</a>
                </nav>
            </div>

            <div
                class="border-t border-white/5 mt-12 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-white/40 font-mono text-[10px] tracking-widest uppercase text-center md:text-left">
                    A / AA / AAA &nbsp;·&nbsp; Laravel 10 / 11 / 12 &nbsp;·&nbsp; PHP 8.2+
                </div>
                <div class="text-white/35 font-mono text-[10px] text-center md:text-right max-w-md leading-relaxed">
                    Automated tools catch ~20–30% of WCAG issues. Manual testing required for full compliance.
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
