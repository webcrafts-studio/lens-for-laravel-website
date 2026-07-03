<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'Docs' }} — Lens for Laravel</title>
    <meta name="description" content="Lens for Laravel documentation — WCAG accessibility auditor for Laravel.">

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
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600,700,800|instrument-sans:400,500,600"
        rel="stylesheet" />

    <!-- highlight.js (always-dark terminal theme) -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/atom-one-dark.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body class="bg-white dark:bg-black font-sans antialiased">

    {{-- ================================================== --}}
    {{-- TOP NAVBAR                                          --}}
    {{-- ================================================== --}}
    <nav
        class="fixed top-0 inset-x-0 z-50 h-14 bg-white dark:bg-black border-b-2 border-black/10 dark:border-white/10 flex items-center">
        <div class="flex items-center justify-between w-full px-4 md:px-6">
            {{-- Left: hamburger (mobile) + logo --}}
            <div class="flex items-center gap-3">
                <button id="sidebar-toggle" onclick="toggleSidebar()"
                    class="xl:hidden w-9 h-9 flex items-center justify-center border-2 border-black/20 dark:border-white/20 hover:border-black dark:hover:border-white text-black dark:text-white transition-colors font-mono text-base"
                    aria-label="Toggle sidebar">☰</button>

                <a href="/" class="flex items-center gap-1.5">
                    <span
                        class="text-black dark:text-white font-black text-base tracking-[0.15em] uppercase font-mono">LENS FOR</span>
                    <span class="text-[#e53e3e] font-black text-base tracking-[0.15em] uppercase font-mono">LARAVEL</span>
                </a>

                <span class="hidden md:block text-black/20 dark:text-white/20 font-mono text-xs">/ DOCS</span>
                <span class="hidden md:block border border-[#e53e3e]/40 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-widest text-[#e53e3e]">v3.0</span>
            </div>

            {{-- Right: theme toggle + GitHub --}}
            <div class="flex items-center gap-3">
                <button type="button" onclick="openDocsSearch()" aria-label="Search documentation"
                    class="hidden md:flex h-9 min-w-48 items-center justify-between gap-3 border-2 border-black/20 dark:border-white/20 px-3 font-mono text-[10px] uppercase tracking-widest text-black/40 dark:text-white/40 hover:border-black dark:hover:border-white hover:text-black dark:hover:text-white transition-colors">
                    <span>Search docs...</span>
                    <span class="text-[9px] text-black/30 dark:text-white/30">⌘K</span>
                </button>

                <button onclick="toggleTheme()" title="Toggle theme"
                    class="w-9 h-9 flex items-center justify-center border-2 border-black/20 dark:border-white/20 hover:border-black dark:hover:border-white text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white transition-colors font-mono text-base">
                    <span class="dark:hidden" aria-label="Switch to dark">☾</span>
                    <span class="hidden dark:inline" aria-label="Switch to light">☀</span>
                </button>

                <a href="https://github.com/webcrafts-studio/lens-for-laravel"
                    class="hidden sm:flex text-black dark:text-white font-mono text-[10px] border-2 border-black/30 dark:border-white/30 hover:border-black dark:hover:border-white px-3 py-2 hover:bg-black dark:hover:bg-white hover:text-white dark:hover:text-black transition-colors uppercase tracking-widest items-center gap-2">
                    GitHub →
                </a>
            </div>
        </div>
    </nav>

    {{-- ================================================== --}}
    {{-- SIDEBAR OVERLAY (mobile)                            --}}
    {{-- ================================================== --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 xl:hidden hidden" onclick="closeSidebar()"></div>

    {{-- ================================================== --}}
    {{-- LEFT SIDEBAR                                        --}}
    {{-- ================================================== --}}
    <aside id="sidebar"
        class="fixed top-14 left-0 bottom-0 w-60 overflow-y-auto bg-white dark:bg-black border-r-2 border-black/10 dark:border-white/10 z-40 -translate-x-full xl:translate-x-0 transition-transform duration-200">
        {{-- Search --}}
        <div class="p-4 border-b border-black/10 dark:border-white/10">
            <button type="button" onclick="openDocsSearch()"
                class="w-full flex items-center justify-between border-2 border-black/20 dark:border-white/20 px-3 py-2 font-mono text-xs text-black/45 dark:text-white/45 hover:border-black dark:hover:border-white hover:text-black dark:hover:text-white transition-colors">
                <span class="flex items-center gap-2">
                    <span class="text-black/30 dark:text-white/30">/</span>
                    <span>Search docs...</span>
                </span>
                <span class="text-[9px] text-black/30 dark:text-white/30">CTRL K</span>
            </button>
        </div>

        {{-- Navigation --}}
        <nav id="docs-nav" class="p-4 pb-8">
            @foreach ($navigation as $section => $items)
                <div class="mb-5 nav-section">
                    <div
                        class="text-[#e53e3e] text-[9px] font-mono font-bold tracking-[0.35em] uppercase mb-2 px-2 nav-section-label">
                        {{ $section }}
                    </div>
                    @foreach ($items as $item)
                        <a href="{{ route('docs.show', $item['slug']) }}"
                            class="nav-item block py-1.5 px-2 font-mono text-xs transition-colors border-l-2 {{ $currentSlug === $item['slug'] ? 'text-[#e53e3e] border-[#e53e3e] bg-[#e53e3e]/5' : 'text-black/60 dark:text-white/50 border-transparent hover:text-black dark:hover:text-white hover:border-black/20 dark:hover:border-white/20' }}"
                            @if ($currentSlug === $item['slug']) aria-current="page" @endif>
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
            @if ($prev || $next)
                <div class="max-w-3xl mx-auto px-5 md:px-8 pb-16">
                    <div class="border-t-2 border-black/10 dark:border-white/10 pt-8 grid grid-cols-2 gap-4">
                        <div>
                            @if ($prev)
                                <a href="{{ route('docs.show', $prev['slug']) }}"
                                    class="group flex flex-col gap-1 border-2 border-black/10 dark:border-white/10 hover:border-black dark:hover:border-white p-4 transition-colors">
                                    <span
                                        class="text-[9px] font-mono tracking-[0.3em] uppercase text-black/30 dark:text-white/30 group-hover:text-black/60 dark:group-hover:text-white/60">←
                                        Previous</span>
                                    <span
                                        class="font-mono text-xs font-bold text-black dark:text-white group-hover:text-[#e53e3e] transition-colors">{{ $prev['title'] }}</span>
                                </a>
                            @endif
                        </div>
                        <div>
                            @if ($next)
                                <a href="{{ route('docs.show', $next['slug']) }}"
                                    class="group flex flex-col gap-1 items-end text-right border-2 border-black/10 dark:border-white/10 hover:border-black dark:hover:border-white p-4 transition-colors">
                                    <span
                                        class="text-[9px] font-mono tracking-[0.3em] uppercase text-black/30 dark:text-white/30 group-hover:text-black/60 dark:group-hover:text-white/60">Next
                                        →</span>
                                    <span
                                        class="font-mono text-xs font-bold text-black dark:text-white group-hover:text-[#e53e3e] transition-colors">{{ $next['title'] }}</span>
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
                <span class="font-mono text-[10px] text-black/30 dark:text-white/30 uppercase tracking-widest">Laravel
                    Lens Docs</span>
                <a href="/"
                    class="font-mono text-[10px] text-black/30 dark:text-white/30 hover:text-[#e53e3e] transition-colors uppercase tracking-widest">←
                    Back to home</a>
            </div>
        </footer>
    </div>

    {{-- ================================================== --}}
    {{-- RIGHT TOC SIDEBAR                                   --}}
    {{-- ================================================== --}}
    <aside
        class="hidden xl:block fixed top-14 right-0 bottom-0 w-52 overflow-y-auto border-l-2 border-black/10 dark:border-white/10 py-6 px-5">
        @if (count($toc) > 0)
            <div
                class="text-[9px] font-mono font-bold tracking-[0.35em] uppercase text-black/30 dark:text-white/30 mb-4">
                ON THIS PAGE
            </div>
            <nav>
                @foreach ($toc as $heading)
                    <a href="#{{ $heading['id'] }}"
                        class="block py-1 font-mono text-[10px] transition-colors toc-link {{ $heading['level'] === 'h3' ? 'pl-3 text-black/40 dark:text-white/30 hover:text-black/70 dark:hover:text-white/60' : 'text-black/60 dark:text-white/50 hover:text-black dark:hover:text-white' }}">
                        {{ $heading['title'] }}
                    </a>
                @endforeach
            </nav>
        @endif
    </aside>

    {{-- ================================================== --}}
    {{-- DOCS SEARCH MODAL                                  --}}
    {{-- ================================================== --}}
    <div id="docs-search-modal" class="fixed inset-0 z-[80] hidden" role="dialog" aria-modal="true"
        aria-labelledby="docs-search-title">
        <div class="absolute inset-0 bg-black/45 dark:bg-black/70 backdrop-blur-sm" onclick="closeDocsSearch()"></div>
        <div class="relative mx-auto mt-20 w-[calc(100%-2rem)] max-w-2xl">
            <div
                class="overflow-hidden border-2 border-black dark:border-white bg-white dark:bg-black shadow-[8px_8px_0_0_rgba(0,0,0,0.18)] dark:shadow-[8px_8px_0_0_rgba(255,255,255,0.12)]">
                <div class="flex items-center gap-3 border-b-2 border-black/10 dark:border-white/10 px-4">
                    <span class="font-mono text-[#e53e3e] text-sm">/</span>
                    <label id="docs-search-title" for="docs-search-input" class="sr-only">Search docs</label>
                    <input id="docs-search-input" type="text" autocomplete="off" spellcheck="false"
                        placeholder="Search Blade, React, Vue, scan history..."
                        class="h-14 flex-1 bg-transparent font-mono text-sm text-black dark:text-white placeholder-black/30 dark:placeholder-white/30 outline-none">
                    <button type="button" onclick="closeDocsSearch()"
                        class="hidden sm:block border border-black/10 dark:border-white/10 px-2 py-1 font-mono text-[9px] uppercase tracking-widest text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                        ESC
                    </button>
                </div>

                <div class="max-h-[55vh] overflow-y-auto">
                    <div id="docs-search-results" class="divide-y divide-black/10 dark:divide-white/10"></div>
                    <div id="docs-search-empty" class="hidden px-5 py-10 text-center">
                        <p class="font-mono text-xs font-bold uppercase tracking-[0.25em] text-black dark:text-white">
                            No results
                        </p>
                        <p class="mt-2 text-sm text-black/50 dark:text-white/45">
                            Try a different keyword from the documentation content.
                        </p>
                    </div>
                    <div id="docs-search-idle" class="px-5 py-10 text-center">
                        <p class="font-mono text-xs font-bold uppercase tracking-[0.25em] text-black dark:text-white">
                            Search all docs
                        </p>
                        <p class="mt-2 text-sm text-black/50 dark:text-white/45">
                            Results include page titles, sections, and full markdown content.
                        </p>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between border-t border-black/10 dark:border-white/10 px-4 py-2 font-mono text-[9px] uppercase tracking-widest text-black/35 dark:text-white/35">
                    <span>Enter to open</span>
                    <span>Ctrl/⌘ K to search</span>
                </div>
            </div>
        </div>
    </div>

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

        /* ---- Full-content docs search ---- */
        var docsSearchIndex = {{ Illuminate\Support\Js::from($searchIndex) }};
        var docsSearchResults = [];
        var docsSearchSelectedIndex = 0;

        function openDocsSearch() {
            var modal = document.getElementById('docs-search-modal');
            var input = document.getElementById('docs-search-input');

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            closeSidebar();

            setTimeout(function() {
                input.focus();
                input.select();
            }, 20);
        }

        function closeDocsSearch() {
            var modal = document.getElementById('docs-search-modal');
            var input = document.getElementById('docs-search-input');

            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            input.value = '';
            docsSearchResults = [];
            docsSearchSelectedIndex = 0;
            renderDocsSearchResults('');
        }

        function searchDocs(query) {
            var normalizedQuery = query.toLowerCase().trim();

            if (!normalizedQuery) {
                return [];
            }

            var terms = normalizedQuery.split(/\s+/).filter(Boolean);

            return docsSearchIndex.map(function(item) {
                var title = item.title.toLowerCase();
                var section = item.section.toLowerCase();
                var content = item.content.toLowerCase();
                var haystack = title + ' ' + section + ' ' + content;
                var score = 0;

                terms.forEach(function(term) {
                    if (!haystack.includes(term)) {
                        score -= 100;
                        return;
                    }

                    if (title.includes(term)) {
                        score += 80;
                    }

                    if (section.includes(term)) {
                        score += 30;
                    }

                    if (content.includes(term)) {
                        score += 10;
                    }
                });

                return {
                    item: item,
                    score: score,
                    snippet: buildSearchSnippet(item.content, terms),
                };
            }).filter(function(result) {
                return result.score > 0;
            }).sort(function(a, b) {
                return b.score - a.score || a.item.title.localeCompare(b.item.title);
            }).slice(0, 8);
        }

        function buildSearchSnippet(content, terms) {
            var lower = content.toLowerCase();
            var firstMatch = -1;

            terms.some(function(term) {
                firstMatch = lower.indexOf(term);
                return firstMatch !== -1;
            });

            if (firstMatch === -1) {
                return content.slice(0, 160);
            }

            var start = Math.max(0, firstMatch - 70);
            var end = Math.min(content.length, firstMatch + 130);
            var prefix = start > 0 ? '...' : '';
            var suffix = end < content.length ? '...' : '';

            return prefix + content.slice(start, end).trim() + suffix;
        }

        function renderDocsSearchResults(query) {
            var resultsEl = document.getElementById('docs-search-results');
            var idleEl = document.getElementById('docs-search-idle');
            var emptyEl = document.getElementById('docs-search-empty');

            resultsEl.innerHTML = '';
            idleEl.classList.toggle('hidden', Boolean(query));
            emptyEl.classList.toggle('hidden', !query || docsSearchResults.length > 0);

            docsSearchResults.forEach(function(result, index) {
                var link = document.createElement('a');
                link.href = result.item.url;
                link.className = [
                    'block px-5 py-4 transition-colors',
                    index === docsSearchSelectedIndex ?
                    'bg-[#e53e3e]/10 dark:bg-[#e53e3e]/15' :
                    'hover:bg-black/[0.03] dark:hover:bg-white/[0.04]',
                ].join(' ');

                var meta = document.createElement('div');
                meta.className =
                    'mb-1 font-mono text-[9px] font-bold uppercase tracking-[0.3em] text-[#e53e3e]';
                meta.textContent = result.item.section;

                var title = document.createElement('div');
                title.className = 'font-mono text-sm font-bold text-black dark:text-white';
                title.textContent = result.item.title;

                var snippet = document.createElement('p');
                snippet.className = 'mt-1 line-clamp-2 text-sm leading-6 text-black/55 dark:text-white/50';
                snippet.textContent = result.snippet;

                link.appendChild(meta);
                link.appendChild(title);
                link.appendChild(snippet);
                resultsEl.appendChild(link);
            });
        }

        document.getElementById('docs-search-input').addEventListener('input', function() {
            docsSearchResults = searchDocs(this.value);
            docsSearchSelectedIndex = 0;
            renderDocsSearchResults(this.value.trim());
        });

        document.getElementById('docs-search-input').addEventListener('keydown', function(event) {
            if (event.key === 'ArrowDown' && docsSearchResults.length) {
                event.preventDefault();
                docsSearchSelectedIndex = Math.min(docsSearchSelectedIndex + 1, docsSearchResults.length - 1);
                renderDocsSearchResults(this.value.trim());
            }

            if (event.key === 'ArrowUp' && docsSearchResults.length) {
                event.preventDefault();
                docsSearchSelectedIndex = Math.max(docsSearchSelectedIndex - 1, 0);
                renderDocsSearchResults(this.value.trim());
            }

            if (event.key === 'Enter' && docsSearchResults[docsSearchSelectedIndex]) {
                window.location.href = docsSearchResults[docsSearchSelectedIndex].item.url;
            }
        });

        document.addEventListener('keydown', function(event) {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                openDocsSearch();
            }

            if (event.key === 'Escape' && !document.getElementById('docs-search-modal').classList.contains('hidden')) {
                closeDocsSearch();
            }
        });

        /* ---- Code block enhancement ---- */
        document.addEventListener('DOMContentLoaded', function() {
            var LANG_LABELS = {
                'bash': 'BASH',
                'shell': 'SHELL',
                'php': 'PHP',
                'javascript': 'JS',
                'html': 'HTML',
                'json': 'JSON',
                'text': 'OUTPUT',
                'ansi': 'TERMINAL',
                'plaintext': 'OUTPUT',
            };

            document.querySelectorAll('.docs-prose pre > code').forEach(function(code) {
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
            navigator.clipboard.writeText(text).then(function() {
                btn.textContent = 'COPIED';
                setTimeout(function() {
                    btn.textContent = 'COPY';
                }, 1500);
            });
        }

        /* ---- Active TOC highlight on scroll ---- */
        (function() {
            var headings = document.querySelectorAll('.docs-prose h2[id], .docs-prose h3[id]');
            var tocLinks = document.querySelectorAll('.toc-link');
            if (!headings.length || !tocLinks.length) {
                return;
            }

            function onScroll() {
                var scrollY = window.scrollY + 120;
                var active = null;
                headings.forEach(function(h) {
                    if (h.offsetTop <= scrollY) {
                        active = h.id;
                    }
                });
                tocLinks.forEach(function(link) {
                    var isActive = link.getAttribute('href') === '#' + active;
                    link.classList.toggle('text-[#e53e3e]', isActive);
                    link.classList.toggle('font-bold', isActive);
                });
            }
            window.addEventListener('scroll', onScroll, {
                passive: true
            });
            onScroll();
        })();
    </script>

</body>

</html>
