<?php

test('the application returns a successful response', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('UI')
        ->assertSee('Languages')
        ->assertSee('neither axe-core nor Lens proves full WCAG')
        ->assertDontSee('20-30%');
});

test('the website and documentation use the Lens favicon', function () {
    expect(public_path('favicon.svg'))->toBeFile()
        ->and(file_get_contents(public_path('favicon.svg')))->toContain('<svg')
        ->toContain('<path')
        ->toContain('M22.25 12.5h8v31.375h15.5v7H22.25Z')
        ->toContain('#c52b21')
        ->not->toContain('<circle')
        ->and(public_path('favicon.ico'))->toBeFile()
        ->and(filesize(public_path('favicon.ico')))->toBeGreaterThan(0)
        ->and(public_path('favicon-dark.svg'))->toBeFile()
        ->and(file_get_contents(public_path('favicon-dark.svg')))->toContain('#ff8a8a');

    $this->get('/')
        ->assertOk()
        ->assertSee('data-theme-favicon')
        ->assertSee('favicon-dark.svg')
        ->assertSee('updateFavicon')
        ->assertDontSee('favicon.ico');

    $this->get(route('docs.show', ['page' => 'introduction']))
        ->assertOk()
        ->assertSee('data-theme-favicon')
        ->assertSee('favicon-dark.svg')
        ->assertSee('updateFavicon')
        ->assertDontSee('favicon.ico');
});

test('Composer metadata identifies the Lens documentation website', function () {
    $composer = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($composer['name'])->toBe('webcrafts-studio/laravel-lens-website')
        ->and($composer['type'])->toBe('project')
        ->and($composer['description'])->toContain('Lens for Laravel')
        ->and($composer['description'])->not->toContain('skeleton')
        ->and($composer['homepage'])->toBe('https://lens.webcrafts.pl');
});

test('support link appears in the website and documentation footers', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Support my work')
        ->assertSee('https://buycoffee.to/jakub-lipinski', false);

    $this->get(route('docs.show', ['page' => 'introduction']))
        ->assertOk()
        ->assertSee('Support my work')
        ->assertSee('https://buycoffee.to/jakub-lipinski', false);
});

test('website footer credits the author and Webcrafts', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Created by')
        ->assertSee('Jakub Lipiński')
        ->assertSee('https://lipinskijakub.pl/', false)
        ->assertSee('Webcrafts.pl')
        ->assertSee('https://webcrafts.pl/', false);
});

test('the website identifies v3.4 as the current development line', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('v3.4')
        ->assertSee('Local models supported');

    $this->get(route('docs.show', ['page' => 'introduction']))
        ->assertOk()
        ->assertSee('What&#039;s New in v3.4', false)
        ->assertSee('authenticated scans for pages behind login')
        ->assertSee('Version 3 Upgrades');

    $this->get(route('docs.show', ['page' => 'upgrade-v3']))
        ->assertOk()
        ->assertSee('Version 3 Upgrades')
        ->assertSee('v3.4 is the current development line')
        ->assertSee('authenticated scans for pages behind login')
        ->assertSee('v3.0 Foundation')
        ->assertSee('URL-aware history comparisons');
});

test('the v3.2 documentation describes structural source mapping across supported frontends', function () {
    $this->get(route('docs.show', ['page' => 'frontend-support']))
        ->assertOk()
        ->assertSee('Structural Source Mapping in v3.2')
        ->assertSee('same-host rendered links can resolve to a named Laravel route')
        ->assertSee('dynamic React props and Vue bindings')
        ->assertSee('React/Vue structural matches are evaluated before weaker Blade route fallbacks');

    $this->get(route('docs.show', ['page' => 'upgrade-v3']))
        ->assertOk()
        ->assertSee('What&#039;s New in v3.2', false)
        ->assertSee('No new migration or configuration key is required');

    expect(file_get_contents(resource_path('markdown/docs/frontend-support.md')))
        ->toContain("route('home')");
});

test('the CLI documentation covers interactive state scripts', function () {
    $this->get(route('docs.show', ['page' => 'cli-reference']))
        ->assertOk()
        ->assertSee('--states=PATH')
        ->assertSee('requires exactly one URL')
        ->assertSee('cannot be combined with')
        ->assertSee('state: Navigation open');
});

test('the v3 documentation describes the reliable AI Fix contract', function () {
    $this->get(route('docs.show', ['page' => 'ai-fix-engine']))
        ->assertOk()
        ->assertSee('Generation Reliability from v3.0')
        ->assertSee('12000 tokens')
        ->assertSee('1024-token budget')
        ->assertSee('one controlled retry')
        ->assertSee('LENS_FOR_LARAVEL_AI_OLLAMA_MODEL')
        ->assertSee('Only the first reviewed occurrence is replaced')
        ->assertSee('AI Fix applied - pending re-scan')
        ->assertSee('keeps the issue in violation counts')
        ->assertSee('does not claim that axe-core has confirmed the fix');
});

test('the v3.3 documentation explains local Ollama setup and verification', function () {
    $this->get(route('docs.show', ['page' => 'ai-fix-engine']))
        ->assertOk()
        ->assertSee('Local Models with Ollama in v3.3')
        ->assertSee('ollama pull qwen2.5-coder:7b')
        ->assertSee('OLLAMA_URL=http://127.0.0.1:11434')
        ->assertSee('OLLAMA_BASE_URL')
        ->assertSee('curl http://127.0.0.1:11434/api/tags')
        ->assertSee('provider ollama');

    $this->get(route('docs.show', ['page' => 'configuration']))
        ->assertOk()
        ->assertSee('ai_ollama_model')
        ->assertSee('ai_ollama_timeout')
        ->assertSee('LENS_FOR_LARAVEL_AI_OLLAMA_MODEL')
        ->assertSee('LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT')
        ->assertSee('Local or self-hosted Ollama');

    $this->get(route('docs.show', ['page' => 'installation']))
        ->assertOk()
        ->assertSee('composer require laravel/ai --dev')
        ->assertSee('No API key is needed for the default local Ollama server');
});

test('the v3.5 documentation describes the explicit model override and dashboard model note', function () {
    $this->get(route('docs.show', ['page' => 'ai-fix-engine']))
        ->assertOk()
        ->assertSee('Explicit Model Override in v3.5')
        ->assertSee('LENS_FOR_LARAVEL_AI_MODEL=gpt-5.6-luna')
        ->assertSee('at your own responsibility')
        ->assertSee('subtle note with the configured provider');

    $this->get(route('docs.show', ['page' => 'dashboard']))
        ->assertOk()
        ->assertSee('subtle note with the configured provider')
        ->assertSee('LENS_FOR_LARAVEL_AI_MODEL');

    $this->get(route('docs.show', ['page' => 'configuration']))
        ->assertOk()
        ->assertSee('ai_model')
        ->assertSee('LENS_FOR_LARAVEL_AI_MODEL=gpt-5.6-luna')
        ->assertSee('takes precedence');
});

test('the v3.1 documentation describes editable progressive AI fixes and fresh rescans', function () {
    $this->get(route('docs.show', ['page' => 'dashboard']))
        ->assertOk()
        ->assertSee('Fix All A and AA in v3.1')
        ->assertSee('generates up to three suggestions concurrently')
        ->assertSee('Ready proposals can be reviewed and edited while later positions continue loading')
        ->assertSee('Closing the modal aborts outstanding queue requests')
        ->assertSee('unique cache-busting query parameter');

    $this->get(route('docs.show', ['page' => 'ai-fix-engine']))
        ->assertOk()
        ->assertSee('Reviewing and Editing in v3.1')
        ->assertSee('Fix All Queues in v3.1')
        ->assertSee('staggered by 250 milliseconds')
        ->assertSee('Suggestions are reviewed and applied one at a time');

    $this->get(route('docs.show', ['page' => 'frontend-support']))
        ->assertOk()
        ->assertSee('Repeated Element Mapping in v3.1')
        ->assertSee('rendered source attributes')
        ->assertSee('numeric :nth-child(...)');

    $this->get(route('docs.show', ['page' => 'scanning-modes']))
        ->assertOk()
        ->assertSee('Fresh Re-scans in v3.1')
        ->assertSee('__lens_scan')
        ->assertSee('reported issue URL remains the original requested URL');
});

test('the current documentation keeps compatibility and defaults aligned with the package', function () {
    $this->get(route('docs.show', ['page' => 'introduction']))
        ->assertOk()
        ->assertSee('<td>8.2+</td>', false)
        ->assertSee('<td>10, 11, 12, 13</td>', false)
        ->assertSee('0.3.2 or newer');

    $scanningModes = file_get_contents(resource_path('markdown/docs/scanning-modes.md'));

    expect($scanningModes)
        ->toContain("LENS_FOR_LARAVEL_CRAWL_MAX_PAGES', 50")
        ->not->toContain("LENS_FOR_LARAVEL_CRAWL_MAX_PAGES', 100");
});

test('the configuration documents consistent local HTTPS handling', function () {
    $this->get(route('docs.show', ['page' => 'configuration']))
        ->assertOk()
        ->assertSee('sitemap and page requests made by the HTTP crawler')
        ->assertSee('JavaScript-rendered crawling in Chromium')
        ->assertSee('element preview screenshots')
        ->assertSee('production-like certificate validation');
});

test('the v3 documentation defines complete localization coverage', function () {
    $this->get(route('docs.show', ['page' => 'dashboard']))
        ->assertOk()
        ->assertSee('package-owned label and message')
        ->assertSee('PDF exports follow the locale selected in the dashboard session')
        ->assertSee('Descriptions of accessibility rules come from axe-core');

    $this->get(route('docs.show', ['page' => 'upgrade-v3']))
        ->assertOk()
        ->assertSee('complete English, Polish, Spanish, French, and German catalogs');
});
