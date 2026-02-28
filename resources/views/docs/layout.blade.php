<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle ?? 'Docs' }} — Laravel Lens</title>
        <meta name="description" content="Laravel Lens documentation — WCAG accessibility auditor for Laravel.">

        {{-- Anti-FOUC: apply saved theme before first paint --}}
        <script>
            (function () {
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
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600,700,800|instrument-sans:400,500,600" rel="stylesheet" />

        <!-- highlight.js (always-dark terminal theme) -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/atom-one-dark.min.css">

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>

    <body class="bg-white dark:bg-black font-sans antialiased">

        {{-- ================================================== --}}
        {{-- TOP NAVBAR                                          --}}
        {{-- ================================================== --}}
        <nav class="fixed top-0 inset-x-0 z-50 h-14 bg-white dark:bg-black border-b-2 border-black/10 dark:border-white/10 flex items-center">
            <div class="flex items-center justify-between w-full px-4 md:px-6">
                {{-- Left: hamburger (mobile) + logo --}}
                <div class="flex items-center gap-3">
                    <button
                        id="sidebar-toggle"
                        onclick="toggleSidebar()"
                        class="xl:hidden w-9 h-9 flex items-center justify-center border-2 border-black/20 dark:border-white/20 hover:border-black dark:hover:border-white text-black dark:text-white transition-colors font-mono text-base"
                        aria-label="Toggle sidebar"
                    >☰</button>

                    <a href="/" class="flex items-center gap-1.5">
                        <span class="text-black dark:text-white font-black text-base tracking-[0.15em] uppercase font-mono">LARAVEL</span>
                        <span class="text-[#e53e3e] font-black text-base tracking-[0.15em] uppercase font-mono">LENS</span>
                    </a>

                    <span class="hidden md:block text-black/20 dark:text-white/20 font-mono text-xs">/ DOCS</span>
                </div>

                {{-- Right: theme toggle + GitHub --}}
                <div class="flex items-center gap-3">
                    <button
                        onclick="toggleTheme()"
                        title="Toggle theme"
                        class="w-9 h-9 flex items-center justify-center border-2 border-black/20 dark:border-white/20 hover:border-black dark:hover:border-white text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors font-mono text-base"
                    >
                        <span class="dark:hidden" aria-label="Switch to dark">☾</span>
                        <span class="hidden dark:inline" aria-label="Switch to light">☀</span>
                    </button>

                    <a
                        href="https://github.com/laravel-lens/laravel-lens"
                        class="hidden sm:flex text-black dark:text-white font-mono text-[10px] border-2 border-black/30 dark:border-white/30 hover:border-black dark:hover:border-white px-3 py-2 hover:bg-black dark:hover:bg-white hover:text-white dark:hover:text-black transition-colors uppercase tracking-widest items-center gap-2"
                    >
                        GitHub →
                    </a>
                </div>
            </div>
        </nav>

        {{-- ================================================== --}}
        {{-- SIDEBAR OVERLAY (mobile)                            --}}
        {{-- ================================================== --}}
        <div
            id="sidebar-overlay"
            class="fixed inset-0 bg-black/50 z-30 xl:hidden hidden"
            onclick="closeSidebar()"
        ></div>

        {{-- ================================================== --}}
        {{-- LEFT SIDEBAR                                        --}}
        {{-- ================================================== --}}
        <aside
            id="sidebar"
            class="fixed top-14 left-0 bottom-0 w-60 overflow-y-auto bg-white dark:bg-black border-r-2 border-black/10 dark:border-white/10 z-40 -translate-x-full xl:translate-x-0 transition-transform duration-200"
        >
            {{-- Search --}}
            <div class="p-4 border-b border-black/10 dark:border-white/10">
                <div class="relative flex items-center border-2 border-black/20 dark:border-white/20 focus-within:border-[#e53e3e] transition-colors">
                    <span class="pl-3 text-black/30 dark:text-white/30 font-mono text-xs shrink-0">/</span>
                    <input
                        id="docs-search"
                        type="text"
                        placeholder="Search docs..."
                        autocomplete="off"
                        class="flex-1 bg-transparent px-2 py-2 font-mono text-xs text-black dark:text-white placeholder-black/25 dark:placeholder-white/25 outline-none"
                    >
                </div>
            </div>

            {{-- Navigation --}}
            <nav id="docs-nav" class="p-4 pb-8">
                @foreach($navigation as $section => $items)
                    <div class="mb-5 nav-section">
                        <div class="text-[#e53e3e] text-[9px] font-mono font-bold tracking-[0.35em] uppercase mb-2 px-2 nav-section-label">
                            {{ $section }}
                        </div>
                        @foreach($items as $item)
                            <a
                                href="{{ route('docs.show', $item['slug']) }}"
                                data-search="{{ strtolower($item['title']) }}"
                                class="nav-item block py-1.5 px-2 font-mono text-xs transition-colors border-l-2 {{ $currentSlug === $item['slug'] ? 'text-[#e53e3e] border-[#e53e3e] bg-[#e53e3e]/5' : 'text-black/60 dark:text-white/50 border-transparent hover:text-black dark:hover:text-white hover:border-black/20 dark:hover:border-white/20' }}"
                                @if($currentSlug === $item['slug']) aria-current="page" @endif
                            >
                                {{ $item['title'] }}
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </aside>

        {{-- ================================================== --}}
        {{-- MAIN CONTENT WRAPPER                               --}}
        {{-- ================================================== --}}
        <div class="xl:ml-60 xl:mr-52 pt-14 min-h-screen flex flex-col">
            <main class="flex-1">
                <article class="max-w-3xl mx-auto px-5 md:px-8 py-10 md:py-14">
                    <div class="docs-prose">
                        @yield('content')
                    </div>
                </article>

                {{-- Prev / Next navigation --}}
                @if($prev || $next)
                    <div class="max-w-3xl mx-auto px-5 md:px-8 pb-16">
                        <div class="border-t-2 border-black/10 dark:border-white/10 pt-8 grid grid-cols-2 gap-4">
                            <div>
                                @if($prev)
                                    <a
                                        href="{{ route('docs.show', $prev['slug']) }}"
                                        class="group flex flex-col gap-1 border-2 border-black/10 dark:border-white/10 hover:border-black dark:hover:border-white p-4 transition-colors"
                                    >
                                        <span class="text-[9px] font-mono tracking-[0.3em] uppercase text-black/30 dark:text-white/30 group-hover:text-black/60 dark:group-hover:text-white/60">← Previous</span>
                                        <span class="font-mono text-xs font-bold text-black dark:text-white group-hover:text-[#e53e3e] transition-colors">{{ $prev['title'] }}</span>
                                    </a>
                                @endif
                            </div>
                            <div>
                                @if($next)
                                    <a
                                        href="{{ route('docs.show', $next['slug']) }}"
                                        class="group flex flex-col gap-1 items-end text-right border-2 border-black/10 dark:border-white/10 hover:border-black dark:hover:border-white p-4 transition-colors"
                                    >
                                        <span class="text-[9px] font-mono tracking-[0.3em] uppercase text-black/30 dark:text-white/30 group-hover:text-black/60 dark:group-hover:text-white/60">Next →</span>
                                        <span class="font-mono text-xs font-bold text-black dark:text-white group-hover:text-[#e53e3e] transition-colors">{{ $next['title'] }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </main>

            {{-- Footer --}}
            <footer class="border-t border-black/10 dark:border-white/10 px-5 md:px-8 py-6">
                <div class="max-w-3xl mx-auto flex items-center justify-between flex-wrap gap-4">
                    <span class="font-mono text-[10px] text-black/30 dark:text-white/30 uppercase tracking-widest">Laravel Lens Docs</span>
                    <a href="/" class="font-mono text-[10px] text-black/30 dark:text-white/30 hover:text-[#e53e3e] transition-colors uppercase tracking-widest">← Back to home</a>
                </div>
            </footer>
        </div>

        {{-- ================================================== --}}
        {{-- RIGHT TOC SIDEBAR                                   --}}
        {{-- ================================================== --}}
        <aside class="hidden xl:block fixed top-14 right-0 bottom-0 w-52 overflow-y-auto border-l-2 border-black/10 dark:border-white/10 py-6 px-5">
            @if(count($toc) > 0)
                <div class="text-[9px] font-mono font-bold tracking-[0.35em] uppercase text-black/30 dark:text-white/30 mb-4">
                    ON THIS PAGE
                </div>
                <nav>
                    @foreach($toc as $heading)
                        <a
                            href="#{{ $heading['id'] }}"
                            class="block py-1 font-mono text-[10px] transition-colors toc-link {{ $heading['level'] === 'h3' ? 'pl-3 text-black/40 dark:text-white/30 hover:text-black/70 dark:hover:text-white/60' : 'text-black/60 dark:text-white/50 hover:text-black dark:hover:text-white' }}"
                        >
                            {{ $heading['title'] }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </aside>

        {{-- ================================================== --}}
        {{-- SCRIPTS                                             --}}
        {{-- ================================================== --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/highlight.min.js"></script>

        <script>
            /* ---- Sidebar toggle (mobile) ---- */
            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('-translate-x-full');
                document.getElementById('sidebar-overlay').classList.toggle('hidden');
            }
            function closeSidebar() {
                document.getElementById('sidebar').classList.add('-translate-x-full');
                document.getElementById('sidebar-overlay').classList.add('hidden');
            }

            /* ---- Client-side nav search ---- */
            document.getElementById('docs-search').addEventListener('input', function () {
                var q = this.value.toLowerCase().trim();
                document.querySelectorAll('.nav-section').forEach(function (section) {
                    var visible = 0;
                    section.querySelectorAll('.nav-item').forEach(function (link) {
                        var match = !q || link.dataset.search.includes(q);
                        link.style.display = match ? '' : 'none';
                        if (match) { visible++; }
                    });
                    section.style.display = visible === 0 ? 'none' : '';
                });
            });

            /* ---- Code block enhancement ---- */
            document.addEventListener('DOMContentLoaded', function () {
                var LANG_LABELS = {
                    'bash': 'BASH', 'shell': 'SHELL', 'php': 'PHP',
                    'javascript': 'JS', 'html': 'HTML', 'json': 'JSON',
                    'text': 'OUTPUT', 'ansi': 'TERMINAL', 'plaintext': 'OUTPUT',
                };

                document.querySelectorAll('.docs-prose pre > code').forEach(function (code) {
                    var pre = code.parentElement;
                    var rawLang = (code.className.match(/language-(\S+)/) || [])[1] || 'text';
                    var label = LANG_LABELS[rawLang] || rawLang.toUpperCase();

                    /* Wrap pre in container */
                    var wrapper = document.createElement('div');
                    wrapper.className = 'code-block-wrapper';
                    pre.parentNode.insertBefore(wrapper, pre);
                    wrapper.appendChild(pre);

                    /* Build header */
                    var header = document.createElement('div');
                    header.className = 'code-block-header';
                    header.innerHTML =
                        '<span class="code-block-lang">' + label + '</span>' +
                        '<button class="code-block-copy" onclick="copyBlock(this)">COPY</button>';
                    wrapper.insertBefore(header, pre);

                    /* Syntax highlight */
                    if (typeof hljs !== 'undefined') {
                        hljs.highlightElement(code);
                    }
                });
            });

            function copyBlock(btn) {
                var text = btn.closest('.code-block-wrapper').querySelector('code').innerText;
                navigator.clipboard.writeText(text).then(function () {
                    btn.textContent = 'COPIED';
                    setTimeout(function () { btn.textContent = 'COPY'; }, 1500);
                });
            }

            /* ---- Active TOC highlight on scroll ---- */
            (function () {
                var headings = document.querySelectorAll('.docs-prose h2[id], .docs-prose h3[id]');
                var tocLinks = document.querySelectorAll('.toc-link');
                if (!headings.length || !tocLinks.length) { return; }

                function onScroll() {
                    var scrollY = window.scrollY + 120;
                    var active = null;
                    headings.forEach(function (h) {
                        if (h.offsetTop <= scrollY) { active = h.id; }
                    });
                    tocLinks.forEach(function (link) {
                        var isActive = link.getAttribute('href') === '#' + active;
                        link.classList.toggle('text-[#e53e3e]', isActive);
                        link.classList.toggle('font-bold', isActive);
                    });
                }
                window.addEventListener('scroll', onScroll, { passive: true });
                onScroll();
            })();
        </script>

    </body>
</html>
