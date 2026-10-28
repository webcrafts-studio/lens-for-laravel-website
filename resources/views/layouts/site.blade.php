<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lens for Laravel - Accessibility, closer to your code')</title>
    <meta name="description" content="@yield('description', 'Local-first accessibility audits for Laravel. Find WCAG issues, trace them to your source, and review AI fixes with cloud providers or local Ollama.')">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="apple-touch-icon" href="{{ asset('assets/apple-touch-icon.png') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'Lens for Laravel - Accessibility, closer to your code')">
    <meta property="og:description" content="Local audits. Source mapping. AI fixes on your terms.">
    <meta property="og:image" content="{{ asset('assets/social-card.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <script>
        (() => {
            let saved;
            try {
                saved = localStorage.getItem('lens-theme');
            } catch (_) {}
            document.documentElement.classList.toggle('dark', saved === 'dark' || (!saved && matchMedia(
                '(prefers-color-scheme: dark)').matches));
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-consent::head />
</head>

<body id="top" class="font-sans antialiased">
    <a href="#main-content" class="skip-link">Skip to @yield('skip-label', 'main content')</a>
    <div class="site-frame">
        <header class="site-header">
            <x-brand />
            <nav class="main-nav" aria-label="Main navigation">
                <a href="{{ route('home') }}#features">Features</a>
                <a href="{{ route('home') }}#ai-fixes">AI fixes</a>
                <a href="{{ route('home') }}#mcp">MCP</a>
                <a href="{{ route('home') }}#ci-cd">CI/CD</a>
                <a href="{{ route('home') }}#community">References</a>
                <a href="{{ route('docs') }}"
                    @if (request()->routeIs('docs*')) aria-current="page" @endif>Documentation</a>
            </nav>
            <div class="header-actions">
                <details class="mobile-navigation" data-mobile-navigation>
                    <summary class="menu-trigger" aria-expanded="false" aria-label="Main navigation"
                        aria-controls="mobile-site-navigation" title="Menu"><x-icon name="menu"
                            class="menu-open-icon" /><x-icon name="close" class="menu-close-icon" /></summary>
                    <nav id="mobile-site-navigation" class="mobile-nav-panel" aria-label="Mobile navigation">
                        <a href="{{ route('home') }}#features">Features</a>
                        <a href="{{ route('home') }}#ai-fixes">AI fixes</a>
                        <a href="{{ route('home') }}#mcp">MCP</a>
                        <a href="{{ route('home') }}#ci-cd">CI/CD</a>
                        <a href="{{ route('home') }}#community">References</a>
                        <a href="{{ route('docs') }}"
                            @if (request()->routeIs('docs*')) aria-current="page" @endif>Documentation</a>
                        <a class="mobile-github" href="https://github.com/{{ config('services.github.repository') }}"
                            target="_blank" rel="noopener noreferrer" aria-describedby="new-tab-description">GitHub</a>
                    </nav>
                </details>
                <button class="search-trigger" type="button" data-search-open aria-label="Search documentation"
                    aria-keyshortcuts="Meta+K Control+K" title="Search documentation (Command or Control + K)"
                    aria-haspopup="dialog" aria-controls="docs-search"><x-icon name="search" /><span
                        class="search-trigger-label">Search</span></button>
                <button type="button" class="icon-button header-theme-toggle" data-theme-toggle
                    aria-label="Switch to dark mode" aria-pressed="false"><span class="dark:hidden"><x-icon
                            name="moon" /></span><span class="hidden dark:block"><x-icon
                            name="sun" /></span></button>
                <x-github-stars-link :stars="$githubStars" class="header-github" />
            </div>
        </header>
        <div class="product-bar" role="region" aria-label="Product information"><span>Accessibility, closer to your
                code.</span><a href="{{ route('docs.show', 'upgrade-v3') }}">v3.6.0 <x-icon name="arrow-up-right" /></a>
        </div>
        @yield('body')
        <footer class="site-footer">
            <div class="footer-grid">
                <div><x-brand />
                    <p>Accessibility tools<br>for your Laravel workflow.</p><span class="micro">Open source /
                        MIT</span>
                </div>
                <nav aria-label="Documentation footer">
                    <h2>Build with Lens</h2><a href="{{ route('docs.show', 'installation') }}">Installation</a><a
                        href="{{ route('docs.show', 'quick-start') }}">Quick start</a><a
                        href="{{ route('docs.show', 'ai-fix-engine') }}">AI Fix Engine</a><a
                        href="{{ route('docs.show', 'cli-reference') }}">CLI reference</a><a
                        href="{{ route('docs.show', 'mcp') }}">Lens MCP</a>
                </nav>
                <nav aria-label="Project footer">
                    <h2>Project</h2><a href="https://github.com/webcrafts-studio/lens-for-laravel" target="_blank"
                        rel="noopener noreferrer" aria-describedby="new-tab-description">GitHub</a><a
                        href="https://github.com/webcrafts-studio/lens-for-laravel/issues" target="_blank"
                        rel="noopener noreferrer" aria-describedby="new-tab-description">Report an issue</a><a
                        href="https://buycoffee.to/jakub-lipinski">Support my work</a><a
                        href="{{ route('home') }}#community">In the community</a><a
                        href="{{ route('privacy') }}" @if (request()->routeIs('privacy')) aria-current="page" @endif>Privacy policy</a>
                </nav>
            </div>
            <div class="footer-bottom">
                <p>Created by <a class="footer-author" href="https://lipinskijakub.pl/" target="_blank"
                        rel="noopener noreferrer" aria-describedby="new-tab-description">Jakub Lipiński</a></p>
                <p class="footer-audit-note">axe-core automates many high-confidence checks, but neither axe-core nor
                    Lens proves full WCAG conformance. Manual testing remains required.</p>
            </div>
        </footer>
    </div>
    <dialog id="docs-search" aria-labelledby="search-title" class="search-dialog" aria-modal="true">
        <div class="search-heading">
            <h2 id="search-title">Search documentation</h2><button type="button" class="icon-button"
                data-search-close aria-label="Close search"><x-icon name="close" /></button>
        </div>
        <label for="docs-search-input" class="sr-only">Search all documentation</label>
        <input type="search" id="docs-search-input" placeholder="Try Ollama, source mapping, or CI..."
            autocomplete="off" aria-controls="search-results" aria-describedby="search-help">
        <p id="search-status" class="search-status" role="status" aria-atomic="true">Search across all
            documentation pages.</p>
        <ul id="search-results" class="search-results" role="list"></ul>
        <p id="search-help" class="search-help">Tab to explore results. Escape to close. Ctrl / ⌘ K to search.</p>
    </dialog>
    <script type="application/json" id="docs-search-index">{!! json_encode($searchIndex, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <div id="copy-status" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></div>
    <span id="new-tab-description" hidden>Opens in a new tab.</span>
    <x-consent::banner />
</body>

</html>
