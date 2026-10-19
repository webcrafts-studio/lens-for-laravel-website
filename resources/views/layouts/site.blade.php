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
            try { saved = localStorage.getItem('lens-theme'); } catch (_) {}
            document.documentElement.classList.toggle('dark', saved === 'dark' || (!saved && matchMedia('(prefers-color-scheme: dark)').matches));
        })();
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|jetbrains-mono:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="top" class="font-sans antialiased">
    <a href="#main-content" class="skip-link">Skip to @yield('skip-label', 'main content')</a>
    <div class="site-frame">
        <header class="site-header">
            <x-brand/>
            <nav class="main-nav" aria-label="Primary navigation">
                <a href="{{ route('home') }}#features">Features</a>
                <a href="{{ route('home') }}#ai-fixes">AI fixes</a>
                <a href="{{ route('docs') }}" @if(request()->routeIs('docs*')) aria-current="page" @endif>Documentation</a>
            </nav>
            <div class="header-actions">
                <button type="button" class="icon-button" data-search-open aria-label="Search documentation"><x-icon name="search"/></button>
                <button type="button" class="icon-button" data-theme-toggle aria-label="Switch to dark mode" aria-pressed="false"><span class="dark:hidden"><x-icon name="moon"/></span><span class="hidden dark:block"><x-icon name="sun"/></span></button>
                <x-github-stars-link :stars="$githubStars" class="header-github"/>
                <button type="button" class="icon-button mobile-menu-toggle" data-menu-toggle aria-controls="mobile-nav" aria-expanded="false" aria-label="Open navigation"><x-icon name="menu"/></button>
            </div>
        </header>
        <nav id="mobile-nav" class="mobile-nav" aria-label="Mobile navigation" hidden>
            <a href="{{ route('home') }}#features">Features</a>
            <a href="{{ route('home') }}#ai-fixes">AI fixes</a>
            <a href="{{ route('docs') }}">Documentation</a>
            <a href="https://github.com/webcrafts-studio/lens-for-laravel">GitHub</a>
        </nav>
        <div class="product-bar" role="region" aria-label="Product information"><span>Accessibility, closer to your code.</span><a href="{{ route('docs.show', 'upgrade-v3') }}">v3.5 <span aria-hidden="true">↗</span></a></div>
        @yield('body')
        <footer class="site-footer">
            <div class="footer-grid">
                <div><x-brand/><p>Accessibility tools<br>for your Laravel workflow.</p><span class="micro">Open source / MIT</span></div>
                <nav aria-label="Documentation footer"><h2>Build with Lens</h2><a href="{{ route('docs.show', 'installation') }}">Installation</a><a href="{{ route('docs.show', 'quick-start') }}">Quick start</a><a href="{{ route('docs.show', 'ai-fix-engine') }}">AI Fix Engine</a><a href="{{ route('docs.show', 'cli-reference') }}">CLI reference</a></nav>
                <nav aria-label="Project footer"><h2>Project</h2><a href="https://github.com/webcrafts-studio/lens-for-laravel">GitHub</a><a href="https://github.com/webcrafts-studio/lens-for-laravel/issues">Report an issue</a><a href="https://buycoffee.to/jakub-lipinski">Support my work</a></nav>
            </div>
            <div class="footer-bottom"><p>Created by <a href="https://lipinskijakub.pl/">Jakub Lipiński</a> / <a href="https://webcrafts.pl/">Webcrafts.pl</a></p><a href="#top" data-back-top>Back to top ↑</a></div>
            <p class="audit-note">axe-core automates many high-confidence checks, but neither axe-core nor Lens proves full WCAG conformance. Manual testing remains required.</p>
        </footer>
    </div>
    <dialog id="docs-search" aria-labelledby="search-title" class="search-dialog" aria-modal="true">
        <div class="search-heading"><h2 id="search-title">Search documentation</h2><button type="button" class="icon-button" data-search-close aria-label="Close search"><x-icon name="close"/></button></div>
        <label for="docs-search-input" class="sr-only">Search all documentation</label>
        <input type="search" id="docs-search-input" placeholder="Try Ollama, source mapping, or CI..." autocomplete="off">
        <p id="search-status" class="search-status" role="status">Search across all documentation pages.</p>
        <ul id="search-results" class="search-results"></ul>
        <p class="search-help">Tab to explore results. Escape to close. Ctrl / ⌘ K to search.</p>
    </dialog>
    <script type="application/json" id="docs-search-index">{!! json_encode($searchIndex, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <div id="copy-status" class="copy-status" role="status" aria-live="polite"></div>
</body>
</html>
