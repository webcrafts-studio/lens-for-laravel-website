<?php

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

test('the application returns a successful response', function () {
    $response = $this->get('/')
        ->assertOk()
        ->assertSee('Find accessibility issues.')
        ->assertSee('Eight supported providers')
        ->assertDontSee('20-30%');

    $text = preg_replace('/\s+/', ' ', strip_tags($response->getContent()));
    expect($text)->toContain('neither axe-core nor Lens proves full WCAG');
});

test('the header displays the live GitHub star count', function () {
    Cache::forget('github-stars:'.config('services.github.repository'));
    Http::fake([
        'https://api.github.com/repos/webcrafts-studio/lens-for-laravel' => Http::response([
            'stargazers_count' => 84000,
        ]),
    ]);

    foreach (['/', route('docs.show', ['page' => 'introduction'])] as $url) {
        $response = $this->get($url)->assertOk();
        $document = new DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new DOMXPath($document);
        $githubLinks = $xpath->query('//header//a[contains(concat(" ", normalize-space(@class), " "), " github-link ")]');

        expect($githubLinks->length)->toBe(1);

        $githubLink = $githubLinks->item(0);

        expect($githubLink->getAttribute('href'))->toBe('https://github.com/webcrafts-studio/lens-for-laravel')
            ->and($githubLink->getAttribute('aria-label'))->toBe('View Lens for Laravel on GitHub - 84K stars')
            ->and($githubLink->getAttribute('title'))->toBe('84K stars on GitHub');

        $starCountsWithIcon = $xpath->query('./span[@class="github-stars"]/svg[@aria-hidden="true"]/following-sibling::*[1][self::span[@class="star-count"] and normalize-space()="84K"]', $githubLink);

        expect($starCountsWithIcon->length)->toBe(1);
    }

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://api.github.com/repos/webcrafts-studio/lens-for-laravel'
            && $request->hasHeader('X-GitHub-Api-Version', '2022-11-28');
    });
    Http::assertSentCount(1);
});

test('GitHub star counts preserve significant zeroes when abbreviated', function (?int $stars, string $formattedStars): void {
    $html = Blade::render('<x-github-stars-link :stars="$stars" />', ['stars' => $stars]);

    expect($html)->toContain('>'.$formattedStars.'</span>');
})->with([
    [null, '-'],
    [0, '0'],
    [999, '999'],
    [1000, '1K'],
    [1500, '1.5K'],
    [10000, '10K'],
    [20000, '20K'],
    [84000, '84K'],
    [100000, '100K'],
    [1000000, '1M'],
    [10500000, '10.5M'],
]);

test('GitHub failures display an accessible fallback and are cached briefly', function (string $failure): void {
    Http::swap(new Factory);
    Http::preventStrayRequests();
    $response = match ($failure) {
        'connection' => Http::failedConnection(),
        'status' => Http::response([], 503),
        'invalid' => Http::response(['stargazers_count' => 'unknown']),
        'negative' => Http::response(['stargazers_count' => -1]),
    };
    Http::fake(['https://api.github.com/repos/webcrafts-studio/lens-for-laravel' => $response]);

    $this->get('/')
        ->assertOk()
        ->assertSee('aria-label="View Lens for Laravel on GitHub"', false)
        ->assertSee('>-</span>', false);

    $this->get(route('docs.show', ['page' => 'introduction']))->assertOk();
    Http::assertSentCount(1);

    $this->travel(6)->minutes();
    $this->get('/')->assertOk();
    Http::assertSentCount(2);
})->with(['connection', 'status', 'invalid', 'negative']);

test('GitHub counts expire after six hours and requests use the configured repository and token', function (): void {
    config()->set('services.github.repository', 'example/repository');
    config()->set('services.github.token', 'test-token');
    Http::fake([
        'https://api.github.com/repos/example/repository' => Http::sequence()
            ->push(['stargazers_count' => 10000])
            ->push(['stargazers_count' => 20000]),
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('href="https://github.com/example/repository"', false)
        ->assertSee('>10K</span>', false);

    $this->travel(5)->hours();
    $this->get('/')->assertOk()->assertSee('>10K</span>', false);
    Http::assertSentCount(1);

    $this->travel(2)->hours();
    $this->get('/')->assertOk()->assertSee('>20K</span>', false);
    Http::assertSentCount(2);
    Http::assertSent(fn (Request $request): bool => $request->hasHeader('Authorization', 'Bearer test-token'));
});

test('the website and documentation use the Lens favicon', function () {
    foreach (['favicon.svg', 'favicon-dark.svg', 'assets/lens-mark.svg'] as $file) {
        $svg = simplexml_load_file(public_path($file));
        expect((string) $svg['viewBox'])->toBe('0 0 64 64')
            ->and((string) $svg->rect['fill'])->toBe('#c52b21')
            ->and((string) $svg->circle['stroke-width'])->toBe('3.5');
    }

    expect(public_path('favicon.ico'))->toBeFile()
        ->and(filesize(public_path('favicon.ico')))->toBeGreaterThan(0);

    foreach (['/', route('docs.show', 'introduction')] as $url) {
        $this->get($url)->assertSuccessful()
            ->assertSee('favicon.svg')
            ->assertSee('apple-touch-icon.png')
            ->assertSee('assets/lens-mark.svg')
            ->assertSee('social-card.png');
    }
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
        ->and($composer['homepage'])->toBe('https://lens.lipinskijakub.pl');
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

test('website and documentation footers credit only Jakub Lipiński', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertSee('Created by')
        ->assertSee('Jakub Lipiński')
        ->assertSee('https://lipinskijakub.pl/', false)
        ->assertSee('Manual testing remains required.')
        ->assertDontSee('Back to top')
        ->assertDontSee('data-back-top', false)
        ->assertDontSee('Webcrafts.pl')
        ->assertDontSee('https://webcrafts.pl/', false);
})->with(['/', '/docs/introduction']);

test('GitHub and author links open safely in a new tab without changing documentation navigation', function (string $url) {
    $response = $this->get($url)->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $links = $xpath->query('//a[starts-with(@href,"https://github.com/") or @href="https://lipinskijakub.pl/"]');

    expect($links->length)->toBeGreaterThanOrEqual(4);

    foreach ($links as $link) {
        expect($link->getAttribute('target'))->toBe('_blank');
        expect(explode(' ', $link->getAttribute('rel')))->toContain('noopener', 'noreferrer');
        expect($document->getElementById($link->getAttribute('aria-describedby'))->textContent)->toBe('Opens in a new tab.');
    }

    foreach ($xpath->query('//a[starts-with(@href,"'.route('docs').'")]') as $link) {
        expect($link->hasAttribute('target'))->toBeFalse();
    }

    if ($url === '/docs/introduction') {
        expect($xpath->query('//article//a[@href="https://github.com/dequelabs/axe-core" and @target="_blank"]')->length)->toBe(1);
    }
})->with(['/', '/docs/introduction', '/docs/ai-fix-engine']);

test('the website identifies v3.6.0 as the current release', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('v3.6.0')
        ->assertDontSee('>v3.5<', false)
        ->assertSee('Local models supported');

    $this->get(route('docs.show', ['page' => 'introduction']))
        ->assertOk()
        ->assertSee('What&#039;s New in v3.5', false)
        ->assertSee('Explicit model selection')
        ->assertSee('Version 3 Upgrades');

    $this->get(route('docs.show', ['page' => 'upgrade-v3']))
        ->assertOk()
        ->assertSee('Version 3 Upgrades')
        ->assertSee('Lens v3.6.0 adds MCP integration')
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
        ->assertSee('subtle note with the configured provider')
        ->assertSee('no longer pins temperature')
        ->assertSee('bulk apply action')
        ->assertSee('accepted individually');

    $this->get(route('docs.show', ['page' => 'dashboard']))
        ->assertOk()
        ->assertSee('subtle note with the configured provider')
        ->assertSee('LENS_FOR_LARAVEL_AI_MODEL');

    $this->get(route('docs.show', ['page' => 'configuration']))
        ->assertOk()
        ->assertSee('ai_model')
        ->assertSee('LENS_FOR_LARAVEL_AI_MODEL=gpt-5.6-luna')
        ->assertSee('takes precedence');

    $this->get(route('docs.show', ['page' => 'upgrade-v3']))
        ->assertOk()
        ->assertSee('What&#039;s New in v3.5', false)
        ->assertSee('optional explicit AI model override');

    $this->get(route('docs.show', ['page' => 'introduction']))
        ->assertOk()
        ->assertSee('What&#039;s New in v3.5', false)
        ->assertSee('Explicit model selection')
        ->assertSee('Fix All bulk apply')
        ->assertSee('Provider-default temperature');
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
        ->assertSee('Suggestions require review before application.');

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
        ->assertSee('>=0.3.2 <0.12')
        ->assertSee('^1.0');

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

test('v3.6 MCP documentation is discoverable and describes the real tool catalog', function (): void {
    $response = $this->get('/docs/mcp')->assertSuccessful()
        ->assertSee('Lens MCP')
        ->assertSee('Laravel 10.49+')
        ->assertSee('composer require laravel/mcp --dev')
        ->assertSee('php artisan lens:mcp:install --client=codex')
        ->assertSee('php artisan lens:mcp:install --client=claude')
        ->assertSee('The client starts')
        ->assertSee('automatically')
        ->assertSee('asynchronous execution by default')
        ->assertSee('lens:mcp:runs work')
        ->assertSee('its own model')
        ->assertSee('pending verification')
        ->assertSee('partial')
        ->assertSee('per-process override');

    $markdown = file_get_contents(resource_path('markdown/docs/mcp.md'));
    preg_match_all('/^\| `(lens_[a-z_]+)` \|/m', $markdown, $matches);
    $documentedTools = $matches[1];
    $actualTools = array_keys(app(\LensForLaravel\LensForLaravel\Mcp\ToolCatalog::class)->definitions());
    sort($documentedTools);
    sort($actualTools);
    expect($documentedTools)->toBe($actualTools);

    $index = app(\App\Documentation::class)->searchIndex();
    expect(collect($index)->firstWhere('slug', 'mcp')['content'])->toContain('lens_scan', 'lens_verify');
    $response->assertSee('href="'.route('docs.show', 'mcp').'"', false);
});

test('v3.6 installation and upgrade instructions keep optional setup and history explicit', function (): void {
    $this->get('/docs/installation')->assertSuccessful()
        ->assertSee('php artisan lens:install')
        ->assertSee('Package discovery and Composer updates do not show this welcome automatically')
        ->assertSee('does not install SDKs, publish files')
        ->assertSee('Optional MCP Installation')
        ->assertSee('Composer selects a compatible version')
        ->assertSee('LENS_FOR_LARAVEL_AI_MODEL');

    $this->get('/docs/upgrade-v3')->assertSuccessful()
        ->assertSee('What&#039;s New in v3.6.0', false)
        ->assertSee('nullable, unique')
        ->assertSee('run_id')
        ->assertSee('Existing scan rows and issue data are preserved')
        ->assertSee('not a migration or config publisher')
        ->assertSee('Composer honors')
        ->assertSee('What&#039;s New in v3.5', false)
        ->assertSee('v3.0 Foundation');
});

test('v3.6 documents every public MCP configuration default and SDK generation', function (): void {
    $response = $this->get('/docs/configuration')->assertSuccessful();
    foreach (array_keys(config('lens-for-laravel.mcp')) as $setting) {
        $response->assertSee($setting);
    }
    $response->assertSee('LENS_FOR_LARAVEL_MCP_ALLOW_WRITES')
        ->assertSee('per-process override')
        ->assertSee('16777216')
        ->assertSee('131072');

    $this->get('/docs/ai-fix-engine')->assertSuccessful()
        ->assertSee('stable')
        ->assertSee('^1.0')
        ->assertSee('thinking_level=low')
        ->assertSee('1024-token budget')
        ->assertSee('one controlled retry')
        ->assertSee('filtered content')
        ->assertSee('no source file is changed');
});
